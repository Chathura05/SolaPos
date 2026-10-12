<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Showroom;
use App\Models\ShowroomReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class PosSystemRegressionTest extends \Tests\TestCase
{
    use RefreshDatabase;

    private Showroom $showroom;
    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->showroom = Showroom::create(['name' => 'Review showroom']);
        $this->cashier = User::factory()->create(['showroom_id' => $this->showroom->id]);
        $this->cashier->assignRole('Cashier');
        $category = Category::create(['name' => 'Review category', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id, 'name' => 'Review product',
            'cost_price' => 50, 'selling_price' => 100, 'is_active' => true,
        ]);
        $this->product->showrooms()->attach($this->showroom->id, ['stock_quantity' => 5]);
        $this->actingAs($this->cashier);
    }

    private function checkout(array $overrides = [])
    {
        return $this->postJson('/pos/checkout', array_replace([
            'items' => [['id' => $this->product->id, 'qty' => 2]],
            'discount_type' => 'fixed', 'discount_value' => 0,
            'tax_percent' => 0, 'paid_amount' => 200, 'payment_method' => 'cash',
        ], $overrides));
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['showroom_id' => $this->showroom->id]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);
        return $admin;
    }

    public function test_receipt_is_accessible_after_successful_checkout(): void
    {
        $saleId = $this->checkout()->assertOk()->json('sale_id');
        $this->get('/pos/receipt/' . $saleId)->assertOk();
    }

    public function test_duplicate_product_rows_cannot_sell_more_than_available_stock(): void
    {
        $response = $this->checkout([
            'items' => [
                ['id' => $this->product->id, 'qty' => 4],
                ['id' => $this->product->id, 'qty' => 4],
            ],
            'paid_amount' => 800,
        ]);
        $stock = DB::table('showroom_product')->where('showroom_id', $this->showroom->id)
            ->where('product_id', $this->product->id)->value('stock_quantity');
        $this->assertSame(422, $response->status(), 'Checkout accepted 8 units from stock 5; remaining stock = ' . $stock);
    }

    public function test_partial_refund_uses_the_price_actually_paid_after_discount(): void
    {
        $saleId = $this->checkout(['discount_value' => 100, 'paid_amount' => 100])
            ->assertOk()->json('sale_id');
        $saleItem = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $response = $this->postJson('/pos/refund-partial/' . $saleId, [
            'items' => [['id' => $saleItem->id, 'qty' => 1]],
        ])->assertOk();
        $this->assertEquals(50, $response->json('refunded_amount'));
    }

    public function test_full_refund_reverses_customer_debt_and_records_refunded_amount(): void
    {
        $customer = Customer::create([
            'name' => 'Review customer', 'showroom_id' => $this->showroom->id,
            'loyalty_points' => 100,
        ]);
        $saleId = $this->checkout(['customer_id' => $customer->id, 'paid_amount' => 100])
            ->assertOk()->json('sale_id');
        $this->assertEquals(100, $customer->fresh()->balance);
        $this->admin();
        $this->post('/pos/refund/' . $saleId)->assertRedirect();
        $this->assertEquals(
            ['balance' => 0, 'points' => 100, 'refunded_amount' => 200],
            ['balance' => $customer->fresh()->balance, 'points' => $customer->fresh()->loyalty_points,
                'refunded_amount' => Sale::findOrFail($saleId)->refunded_amount],
            'Full refund must reconcile customer debt, earned points, and refund totals.'
        );
    }

    public function test_cashier_cannot_open_another_showrooms_return(): void
    {
        $otherShowroom = Showroom::create(['name' => 'Other showroom']);
        $return = ShowroomReturn::create([
            'reference_number' => 'RET-REVIEW-OTHER',
            'showroom_id' => $otherShowroom->id, 'status' => 'pending',
            'notes' => 'Other showroom confidential return',
        ]);
        $this->get('/returns/' . $return->id)->assertForbidden();
    }

    public function test_loyalty_redemption_matches_the_amount_displayed_by_the_pos(): void
    {
        $customer = Customer::create([
            'name' => 'Loyalty customer', 'showroom_id' => $this->showroom->id,
            'loyalty_points' => 20,
        ]);
        $saleId = $this->checkout([
            'items' => [['id' => $this->product->id, 'qty' => 1]],
            'customer_id' => $customer->id, 'points_redeemed' => 10,
            'tax_percent' => 10, 'paid_amount' => 99,
        ])->assertOk()->json('sale_id');
        // The POS displays (100 - 10 points) * 1.10 = 99.
        $this->assertEquals(99, Sale::findOrFail($saleId)->total_amount);
        $this->assertEquals(0, $customer->fresh()->balance);
    }

    public function test_return_cannot_remove_more_stock_than_is_available(): void
    {
        $return = ShowroomReturn::create([
            'reference_number' => 'RET-REVIEW-EXCESS',
            'showroom_id' => $this->showroom->id, 'status' => 'pending',
        ]);
        $return->items()->create(['product_id' => $this->product->id, 'quantity' => 8]);
        $this->admin();
        $this->post('/returns/' . $return->id . '/accept')->assertRedirect();
        $this->assertSame('pending', $return->fresh()->status, 'Return accepted 8 units despite only 5 in stock.');
    }

    public function test_standard_seeding_produces_an_admin_with_a_login_username(): void
    {
        // Roles were created for the other fixtures; clear them for fresh-install seeding.
        DB::table('model_has_roles')->delete();
        DB::table('roles')->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertSame('admin', $admin->username);
    }

    public function test_deleting_a_cashier_preserves_their_completed_sales(): void
    {
        $saleId = $this->checkout()->assertOk()->json('sale_id');
        $this->admin();
        $this->delete('/users/' . $this->cashier->id)->assertRedirect();
        $this->assertDatabaseHas('sales', ['id' => $saleId]);
        $this->assertSoftDeleted($this->cashier);
        $this->assertSame($this->cashier->name, Sale::findOrFail($saleId)->cashier->name);
        $this->assertDatabaseCount('sale_items', 1);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_deleted_cashier_cannot_log_in(): void
    {
        $this->cashier->delete();
        auth()->logout();
        $this->post('/login', ['username' => $this->cashier->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_partial_then_full_refund_restores_only_remaining_units(): void
    {
        $saleId = $this->checkout(['discount_value' => 100, 'paid_amount' => 100])->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 1]]])
            ->assertOk()->assertJsonPath('refunded_amount', 50);
        $this->assertSame('partially_refunded', Sale::findOrFail($saleId)->status);
        $this->post('/pos/refund/' . $saleId)->assertRedirect()->assertSessionHas('success');
        $this->assertSame('refunded', Sale::findOrFail($saleId)->status);
        $this->assertEquals(100, Sale::findOrFail($saleId)->refunded_amount);
        $this->assertEquals(2, $item->fresh()->refunded_quantity);
        $this->assertStock(5);
        $this->post('/pos/refund/' . $saleId)->assertRedirect()->assertSessionHas('error');
        $this->assertStock(5);
    }

    public function test_refunds_include_tax_and_preserve_rounding_over_separate_returns(): void
    {
        $saleId = $this->checkout(['discount_value' => 100, 'tax_percent' => 10, 'paid_amount' => 110])
            ->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 1]]])
            ->assertOk()->assertJsonPath('refunded_amount', 55);

        $this->actingAs($this->cashier);
        $this->product->update(['selling_price' => 0.01]);
        $saleId = $this->checkout([
            'items' => [['id' => $this->product->id, 'qty' => 3]],
            'discount_value' => 0.01, 'paid_amount' => 0.02,
        ])->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $sum = 0;
        for ($i = 0; $i < 3; $i++) {
            $sum += $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 1]]])
                ->assertOk()->json('refunded_amount');
        }
        $this->assertEquals(0.02, $sum);
        $this->assertEquals(0.02, Sale::findOrFail($saleId)->refunded_amount);
        $this->assertSame('refunded', Sale::findOrFail($saleId)->status);
    }

    public function test_refund_rejects_duplicate_and_excess_quantities_without_changing_stock(): void
    {
        $saleId = $this->checkout()->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [
            ['id' => $item->id, 'qty' => 1], ['id' => $item->id, 'qty' => 1],
        ]])->assertUnprocessable();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 3]]])
            ->assertUnprocessable();
        $this->assertStock(3);
        $this->assertEquals(0, $item->fresh()->refunded_quantity);
        $this->assertEquals(0, Sale::findOrFail($saleId)->refunded_amount);
    }

    public function test_partial_refunds_restore_redeemed_points_once(): void
    {
        $customer = Customer::create(['name' => 'Loyalty refund', 'showroom_id' => $this->showroom->id, 'loyalty_points' => 20]);
        $saleId = $this->checkout(['customer_id' => $customer->id, 'points_redeemed' => 20, 'paid_amount' => 180])
            ->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 1]]])
                ->assertOk()->assertJsonPath('refunded_amount', 90);
        }
        $this->assertEquals(20, $customer->fresh()->loyalty_points);
        $this->assertEquals(180, Sale::findOrFail($saleId)->refunded_amount);
    }

    public function test_refund_cancels_unpaid_debt_and_reports_cash_portion(): void
    {
        $customer = Customer::create(['name' => 'Credit refund', 'showroom_id' => $this->showroom->id, 'balance' => 50]);
        $saleId = $this->checkout(['customer_id' => $customer->id, 'paid_amount' => 100])->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->first();
        $this->admin();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 2]]])
            ->assertOk()->assertJsonPath('balance_cancelled', 100)->assertJsonPath('cash_refund', 100);
        $this->assertEquals(50, $customer->fresh()->balance);
    }

    public function test_authorized_customer_payment_and_receipt_email_work(): void
    {
        $customer = Customer::create(['name' => 'Customer payment', 'showroom_id' => $this->showroom->id, 'balance' => 100]);
        $this->get('/customers/' . $customer->id)->assertOk();
        $this->post('/customers/' . $customer->id . '/payments', ['amount' => 25, 'payment_method' => 'cash'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertEquals(75, $customer->fresh()->balance);
        \Illuminate\Support\Facades\Mail::fake();
        $saleId = $this->checkout()->assertOk()->json('sale_id');
        $this->postJson('/pos/send-receipt/' . $saleId, ['email' => 'review@example.com'])->assertOk();
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\SaleReceiptMail::class);
    }

    public function test_cashier_cannot_open_another_showrooms_receipt_or_collect_payment(): void
    {
        $customer = Customer::create(['name' => 'Other customer', 'showroom_id' => $this->showroom->id, 'balance' => 100]);
        $saleId = $this->checkout()->assertOk()->json('sale_id');
        $other = Showroom::create(['name' => 'Other showroom']);
        $this->cashier->update(['showroom_id' => $other->id]);
        $this->get('/pos/receipt/' . $saleId)->assertForbidden();
        $this->post('/customers/' . $customer->id . '/payments', ['amount' => 25, 'payment_method' => 'cash'])->assertForbidden();
        $this->assertEquals(100, $customer->fresh()->balance);
    }

    public function test_standard_seeding_is_repeatable_and_allows_admin_login(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
        auth()->logout();
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs(User::where('email', 'admin@example.com')->firstOrFail());
    }

    public function test_stock_transfer_moves_stock_and_records_both_movement_types(): void
    {
        $destination = Showroom::create(['name' => 'Transfer destination']);
        $this->admin();
        $this->post('/inventory/transfer', [
            'product_id' => $this->product->id, 'source_showroom_id' => $this->showroom->id,
            'dest_showroom_id' => $destination->id, 'quantity' => 3,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertStock(2);
        $this->assertDatabaseHas('showroom_product', ['showroom_id' => $destination->id, 'product_id' => $this->product->id, 'stock_quantity' => 3]);
        $this->assertDatabaseHas('stock_movements', ['type' => 'transfer_out', 'quantity' => -3]);
        $this->assertDatabaseHas('stock_movements', ['type' => 'transfer_in', 'quantity' => 3]);
    }

    public function test_history_safely_embeds_item_names_that_contain_script_end_tags(): void
    {
        $name = '</script><img src=x onerror="window.reviewMarker=1">';
        $this->product->update(['name' => $name]);
        $this->checkout()->assertOk();
        $this->admin();
        $response = $this->get('/pos/history')->assertOk();
        $this->assertStringNotContainsString($name, $response->getContent());
        $this->assertStringContainsString('\\u003C', $response->getContent());
    }

    public function test_return_acceptance_is_atomic_and_cannot_be_repeated(): void
    {
        $return = ShowroomReturn::create(['reference_number' => 'RET-ATOMIC', 'showroom_id' => $this->showroom->id, 'status' => 'pending']);
        $return->items()->create(['product_id' => $this->product->id, 'quantity' => 2]);
        $this->admin();
        $this->post('/returns/' . $return->id . '/accept')->assertRedirect()->assertSessionHas('success');
        $this->assertStock(3);
        $this->post('/returns/' . $return->id . '/accept')->assertRedirect()->assertSessionHasErrors('return');
        $this->assertStock(3);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    private function assertStock(int $quantity): void
    {
        $this->assertEquals($quantity, DB::table('showroom_product')->where('showroom_id', $this->showroom->id)
            ->where('product_id', $this->product->id)->value('stock_quantity'));
    }

    public function test_free_sale_refund_restores_points_by_item_value(): void
    {
        $otherProduct = Product::create(['category_id' => $this->product->category_id, 'name' => 'Lower-price product', 'selling_price' => 25]);
        $otherProduct->showrooms()->attach($this->showroom->id, ['stock_quantity' => 1]);
        $customer = Customer::create(['name' => 'Points-only customer', 'showroom_id' => $this->showroom->id, 'loyalty_points' => 125]);
        $saleId = $this->checkout([
            'items' => [['id' => $this->product->id, 'qty' => 1], ['id' => $otherProduct->id, 'qty' => 1]],
            'customer_id' => $customer->id, 'points_redeemed' => 125, 'paid_amount' => 0,
        ])->assertOk()->json('sale_id');
        $item = Sale::findOrFail($saleId)->items()->where('product_id', $otherProduct->id)->firstOrFail();
        $this->admin();
        $this->postJson('/pos/refund-partial/' . $saleId, ['items' => [['id' => $item->id, 'qty' => 1]]])
            ->assertOk()->assertJsonPath('refunded_amount', 0);
        $this->assertEquals(25, $customer->fresh()->loyalty_points);
        $this->post('/pos/refund/' . $saleId)->assertRedirect()->assertSessionHas('success');
        $this->assertEquals(125, $customer->fresh()->loyalty_points);
    }
}
