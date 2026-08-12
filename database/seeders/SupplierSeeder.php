<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Karachi Gas Industries',
                'company_name' => 'Karachi Gas Industries (Pvt) Ltd',
                'phone' => '021-34567890',
                'email' => 'sales@karachigas.com.pk',
                'address' => 'Site Area, Karachi, Sindh',
                'ntn_number' => '1234567-8',
                'contact_person' => 'Imran Sheikh',
                'contact_person_phone' => '0300-1234567',
                'opening_balance' => 0,
                'balance_type' => 'payable',
                'cylinder_return_days' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Lahore Cylinder Manufacturers',
                'company_name' => 'Lahore Cylinder Manufacturers Ltd',
                'phone' => '042-35678901',
                'email' => 'info@lahorecylinders.com.pk',
                'address' => 'Sundar Industrial Estate, Lahore, Punjab',
                'ntn_number' => '7654321-0',
                'contact_person' => 'Bilal Ahmed',
                'contact_person_phone' => '0301-7654321',
                'opening_balance' => 0,
                'balance_type' => 'payable',
                'cylinder_return_days' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], $supplier);
        }

        $this->command->info('✅ Suppliers seeded successfully!');
        $this->command->info('🚚 Total Suppliers: ' . Supplier::count());
    }
}
