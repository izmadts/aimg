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
        $this->call([
            UserSeeder::class,
            AccountSeeder::class,
        ]);

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

        Customer::create([
            'erp_customer_id' => 'CUST-002',
            'name' => 'Medical Store ABC',
            'phone' => '0300-7654321',
            'address' => 'Gulberg, Lahore',
            'security_deposit' => 0,
            'is_active' => true
        ]);

        // 3. Cylinder types
        $oxygenCylinder = Cylinder::create([
            'cylinder_number' => 'CYL-0001',
            'gas_product_id' => $oxygen->id,
            'type' => 'B-D Type',
            'manufacturer' => 'Pakistan Steel',
            'tare_weight' => 45.5,
            'capacity' => 50,
            'stock_quantity' => 10,
            'issued_quantity' => 0,
            'purchase_price' => 15000,
            'sale_price' => 18000,
            'purchase_date' => now()->subMonths(6),
        ]);
        $oxygenCylinder->updateStatus();

        $nitrogenCylinder = Cylinder::create([
            'cylinder_number' => 'CYL-0002',
            'gas_product_id' => $nitrogen->id,
            'type' => 'D-Type',
            'manufacturer' => 'Pakistan Steel',
            'tare_weight' => 45.5,
            'capacity' => 50,
            'stock_quantity' => 5,
            'issued_quantity' => 0,
            'purchase_price' => 15000,
            'sale_price' => 18000,
            'purchase_date' => now()->subMonths(6),
        ]);
        $nitrogenCylinder->updateStatus();

        // 4. Issue 2 oxygen cylinders to customer1 (for testing)
        $oxygenCylinder->issueToCustomer($customer1->id, 2, 3000, 'SEED-TEST');

        $this->command->info('✅ Seeding completed successfully!');
        $this->command->info('📊 Total Customers: ' . Customer::count());
        $this->command->info('🧪 Total Gas Products: ' . GasProduct::count());
        $this->command->info('🛢️ Total Cylinder Types: ' . Cylinder::count());
        $this->command->info('📌 Cylinders Issued: ' . Cylinder::sum('issued_quantity'));
    }
}
