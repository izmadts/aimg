<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'KG', 'description' => 'Kilogram'],
            ['name' => 'Cubic Meter', 'description' => 'Cubic Meter (m3)'],
            ['name' => 'Liters', 'description' => 'Liters'],
            ['name' => 'Cubic Feet', 'description' => 'Cubic Feet (ft3)'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['name' => $unit['name']], $unit);
        }

        $this->command->info('✅ Units seeded successfully!');
    }
}
