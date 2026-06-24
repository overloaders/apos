<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\CashRegister;
use App\Models\ExpenseCategory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'permissions' => json_encode(['*'])],
            ['name' => 'Manager', 'slug' => 'manager', 'permissions' => json_encode(['reports.view', 'reports.export', 'settings.manage', 'expenses.approve', 'stock.approve'])],
            ['name' => 'Kasir', 'slug' => 'kasir', 'permissions' => json_encode(['pos.access', 'sales.view', 'members.view', 'members.create'])],
            ['name' => 'Gudang', 'slug' => 'gudang', 'permissions' => json_encode(['stock.manage', 'purchasing.manage', 'receiving.manage'])],
            ['name' => 'Purchasing', 'slug' => 'purchasing', 'permissions' => json_encode(['purchasing.manage', 'suppliers.manage'])],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role_id' => Role::where('slug', 'admin')->first()->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Cashier User
        User::updateOrCreate(
            ['email' => 'kasir@pos.test'],
            [
                'name' => 'Kasir 1',
                'username' => 'kasir',
                'password' => Hash::make('password'),
                'role_id' => Role::where('slug', 'kasir')->first()->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Default Warehouse
        Warehouse::updateOrCreate(
            ['code' => 'GUD-001'],
            ['name' => 'Gudang Utama', 'is_main' => true, 'address' => 'Jl. Utama No. 1']
        );

        Warehouse::updateOrCreate(
            ['code' => 'GUD-002'],
            ['name' => 'Gudang Cabang', 'is_main' => false, 'address' => 'Jl. Cabang No. 2']
        );

        // Cash Register
        CashRegister::updateOrCreate(
            ['code' => 'CR-001'],
            ['name' => 'Kasir 1', 'warehouse_id' => Warehouse::where('code', 'GUD-001')->first()->id]
        );

        CashRegister::updateOrCreate(
            ['code' => 'CR-002'],
            ['name' => 'Kasir 2', 'warehouse_id' => Warehouse::where('code', 'GUD-001')->first()->id]
        );

        // Expense Categories
        $expenseCategories = [
            'Gaji Karyawan',
            'Listrik & Air',
            'Sewa Tempat',
            'Perlengkapan Toko',
            'Transport & Pengiriman',
            'Maintenance',
            'Lain-lain',
        ];

        foreach ($expenseCategories as $cat) {
            ExpenseCategory::updateOrCreate(['name' => $cat]);
        }

        // Default company settings
        $settings = [
            'company_name' => 'POS Supermarket',
            'company_address' => 'Jl. Raya Utama No. 123, Kota',
            'company_phone' => '021-1234567',
            'company_email' => 'info@supermarket.com',
            'company_npwp' => '01.234.567.8-901.000',
            'tax_rate' => '11',
            'currency' => 'Rp',
            'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
            'receipt_width' => '80',
        ];

        foreach ($settings as $key => $value) {
            DB::table('company_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'type' => 'text', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
