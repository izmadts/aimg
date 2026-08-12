<?php

namespace Database\Seeders;

use App\Models\CylinderType;
use Illuminate\Database\Seeder;

class CylinderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Extra Small', 'capacity' => 1.7, 'price_premium' => 1500],
            ['name' => 'Small', 'capacity' => 3.4, 'price_premium' => 2000],
            ['name' => 'Medium', 'capacity' => 8.8, 'price_premium' => 3000],
            ['name' => 'Large', 'capacity' => 9.9, 'price_premium' => 3500],
        ];

        foreach ($types as $type) {
            CylinderType::updateOrCreate(['name' => $type['name']], $type);
        }

        $this->command->info('✅ Cylinder Types seeded successfully!');
    }
}
