<?php

namespace Database\Seeders;

use App\Models\Cylinder;
use App\Models\GasProduct;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class CylinderSeeder extends Seeder
{
    /**
     * Standard cylinder sizes carried for every gas, keyed by their water
     * capacity in cubic meters and the size label the business uses on the floor.
     */
    private const SIZES = [
        'XS' => ['label' => 'Extra Small', 'capacity' => 1.70, 'tare_weight' => 15, 'purchase_price' => 4500, 'sale_price' => 6000],
        'S'  => ['label' => 'Small',       'capacity' => 3.40, 'tare_weight' => 35, 'purchase_price' => 7500, 'sale_price' => 9500],
        'M'  => ['label' => 'Medium',      'capacity' => 8.80, 'tare_weight' => 55, 'purchase_price' => 12000, 'sale_price' => 15000],
        'L'  => ['label' => 'Large',       'capacity' => 9.90, 'tare_weight' => 60, 'purchase_price' => 14000, 'sale_price' => 17500],
    ];

    public function run(): void
    {
        $supplier = Supplier::first();
        $gases = GasProduct::all();

        foreach ($gases as $gas) {
            foreach (self::SIZES as $sizeCode => $size) {
                Cylinder::updateOrCreate(
                    ['cylinder_number' => "{$gas->code}-{$sizeCode}"],
                    [
                        'gas_product_id' => $gas->id,
                        'type' => $size['label'],
                        'manufacturer' => 'Lahore Cylinder Manufacturers',
                        'tare_weight' => $size['tare_weight'],
                        'capacity' => $size['capacity'],
                        'stock_quantity' => 20,
                        'issued_quantity' => 0,
                        'filled_quantity' => 20,
                        'status' => 'in_house',
                        'purchase_price' => $size['purchase_price'],
                        'sale_price' => $size['sale_price'],
                        'supplier_id' => $supplier?->id,
                        'purchase_date' => now(),
                        'notes' => "{$size['label']} ({$size['capacity']} m3) cylinder for {$gas->name}.",
                    ]
                );
            }
        }

        $this->command->info('✅ Cylinders seeded successfully!');
        $this->command->info('🛢️ Total Cylinder Types: ' . Cylinder::count());
    }
}
