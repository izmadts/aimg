<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            UnitSeeder::class,
            CylinderTypeSeeder::class,
            GasProductSeeder::class,
            CylinderSeeder::class,
        ]);
    }
}
