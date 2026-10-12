<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist (only Admin and Cashier)
        $adminRole   = Role::firstOrCreate(['name' => 'Admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);

        // Admin
        $admin = User::withTrashed()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'username' => 'admin', 'password' => 'admin123']
        );
        if (!$admin->username && !User::withTrashed()->where('username', 'admin')->exists()) {
            $admin->update(['username' => 'admin']);
        }
        $admin->syncRoles([$adminRole]);

        // Cashier
        $cashier = User::withTrashed()->firstOrCreate(
            ['email' => 'cashier@example.com'],
            ['name' => 'Cashier One', 'username' => 'cashier', 'password' => 'cashier123']
        );
        $cashier->syncRoles([$cashierRole]);
    }
}
