<?php

namespace Database\Seeders;

use App\Models\CylinderType;
use Illuminate\Database\Seeder;

class CylinderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Extra Small', 'capacity' => 1.7],
            ['name' => 'Small', 'capacity' => 3.4],
            ['name' => 'Medium', 'capacity' => 8.8],
            ['name' => 'Large', 'capacity' => 9.9],
        ];

        foreach ($types as $type) {
            CylinderType::updateOrCreate(['name' => $type['name']], $type);
        }

        $this->command->info('✅ Cylinder Types seeded successfully!');
    }
}
