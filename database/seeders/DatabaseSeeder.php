<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\GasProduct;
use App\Models\Cylinder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gas Products
        $oxygen = GasProduct::create([
            'name' => 'Oxygen',
            'code' => 'O2',
            'uom' => 'KG',
            'purchase_price' => 150,
            'sale_price' => 200,
            'current_stock' => 500,
            'minimum_stock_level' => 100,
            'is_active' => true
        ]);

        $nitrogen = GasProduct::create([
            'name' => 'Nitrogen',
            'code' => 'N2',
            'uom' => 'Cubic Meter',
            'purchase_price' => 100,
            'sale_price' => 150,
            'current_stock' => 300,
            'minimum_stock_level' => 50,
            'is_active' => true
        ]);

        // 2. Customers
        $customer1 = Customer::create([
            'erp_customer_id' => 'CUST-001',
            'name' => 'City Hospital',
            'phone' => '0300-1234567',
            'address' => 'Main Boulevard, Lahore',
            'security_deposit' => 50000,
            'is_active' => true
        ]);

        $customer2 = Customer::create([
            'erp_customer_id' => 'CUST-002',
            'name' => 'Medical Store ABC',
            'phone' => '0300-7654321',
            'address' => 'Gulberg, Lahore',
            'security_deposit' => 0,
            'is_active' => true
        ]);

        // 3. Cylinders (10 test cylinders)
        for ($i = 1; $i <= 10; $i++) {
            Cylinder::create([
                'cylinder_number' => 'CYL-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'gas_product_id' => $i <= 7 ? $oxygen->id : $nitrogen->id,
                'type' => $i <= 5 ? 'B-D Type' : 'D-Type',
                'manufacturer' => 'Pakistan Steel',
                'tare_weight' => 45.5,
                'capacity' => 50,
                'current_gas_quantity' => $i <= 3 ? 50 : 0, // 3 filled, rest empty
                'status' => $i <= 3 ? 'in_house_filled' : 'in_house_empty',
                'purchase_price' => 15000,
                'purchase_date' => now()->subMonths(6),
                'last_hydro_test_date' => now()->subMonths(11),
                'next_hydro_test_date' => now()->addMonth()
            ]);
        }

        // 4. Issue 2 cylinders to customer1 (for testing)
        $cylinder1 = Cylinder::where('cylinder_number', 'CYL-0001')->first();
        $cylinder1->update([
            'status' => 'issued',
            'current_customer_id' => $customer1->id
        ]);

        $cylinder2 = Cylinder::where('cylinder_number', 'CYL-0002')->first();
        $cylinder2->update([
            'status' => 'issued',
            'current_customer_id' => $customer1->id
        ]);

        $this->command->info('✅ Seeding completed successfully!');
        $this->command->info('📊 Total Customers: ' . Customer::count());
        $this->command->info('🧪 Total Gas Products: ' . GasProduct::count());
        $this->command->info('🛢️ Total Cylinders: ' . Cylinder::count());
        $this->command->info('📌 Issued Cylinders: ' . Cylinder::where('status', 'issued')->count());
    }
}