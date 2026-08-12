<?php

namespace Database\Seeders;

use App\Models\GasProduct;
use Illuminate\Database\Seeder;

class GasProductSeeder extends Seeder
{
    /**
     * Standard medical/industrial gases carried by the business. Prices are in
     * PKR per cubic meter; stock levels are opening figures for a fresh warehouse.
     */
    public function run(): void
    {
        $gases = [
            [
                'name' => 'Oxygen',
                'code' => 'O2',
                'uom' => 'Cubic Meter',
                'purchase_price' => 25.00,
                'sale_price' => 40.00,
                'current_stock' => 500,
                'minimum_stock_level' => 50,
                'description' => 'Medical & industrial grade oxygen gas.',
                'is_active' => true,
            ],
            [
                'name' => 'Nitrogen',
                'code' => 'N2',
                'uom' => 'Cubic Meter',
                'purchase_price' => 18.00,
                'sale_price' => 30.00,
                'current_stock' => 500,
                'minimum_stock_level' => 50,
                'description' => 'Industrial grade nitrogen gas.',
                'is_active' => true,
            ],
            [
                'name' => 'Argon',
                'code' => 'AR',
                'uom' => 'Cubic Meter',
                'purchase_price' => 180.00,
                'sale_price' => 250.00,
                'current_stock' => 200,
                'minimum_stock_level' => 20,
                'description' => 'Industrial grade argon gas, used for welding shielding.',
                'is_active' => true,
            ],
            [
                'name' => 'Carbon Dioxide',
                'code' => 'CO2',
                'uom' => 'Cubic Meter',
                'purchase_price' => 30.00,
                'sale_price' => 50.00,
                'current_stock' => 300,
                'minimum_stock_level' => 30,
                'description' => 'Industrial & beverage grade carbon dioxide gas.',
                'is_active' => true,
            ],
        ];

        foreach ($gases as $gas) {
            GasProduct::updateOrCreate(['code' => $gas['code']], $gas);
        }

        $this->command->info('✅ Gas products seeded successfully!');
        $this->command->info('🧪 Total Gas Products: ' . GasProduct::count());
    }
}
