<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Mail\LowStockAlertMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:low-stock-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email to Admins about products that are low in stock.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock products...');

        $lowStockProducts = Product::where('is_active', true)
            ->select('products.*')
            ->selectRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) as total_stock')
            ->whereRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) <= reorder_level')
            ->orderBy('total_stock')
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('All stock levels are healthy. No email sent.');
            return;
        }

        $admins = User::role('Admin')->whereNotNull('email')->get();

        if ($admins->isEmpty()) {
            $this->warn('Low stock products found, but no Admin users with valid email addresses exist.');
            return;
        }

        $this->info("Found {$lowStockProducts->count()} low stock products. Sending email to {$admins->count()} Admin(s)...");

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new LowStockAlertMail($lowStockProducts));
                $this->info("Email sent to {$admin->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send low stock alert to {$admin->email}: " . $e->getMessage());
                $this->error("Failed to send email to {$admin->email}");
            }
        }

        $this->info('Low stock alerts process completed.');
    }
}
