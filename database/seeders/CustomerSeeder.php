<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Aga Khan University Hospital',
                'phone' => '021-34930051',
                'email' => 'procurement@aku.edu.pk',
                'address' => 'Stadium Road, Karachi',
                'ntn_number' => '1122334-5',
                'security_deposit' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Shaukat Khanum Memorial Hospital',
                'phone' => '042-35945100',
                'email' => 'purchase@shaukatkhanum.org.pk',
                'address' => 'Johar Town, Lahore',
                'ntn_number' => '2233445-6',
                'security_deposit' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'City Welding Works',
                'phone' => '0300-9876543',
                'email' => null,
                'address' => 'Industrial Area, Faisalabad',
                'ntn_number' => null,
                'security_deposit' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['name' => $customer['name']], $customer);
        }

        $this->command->info('✅ Customers seeded successfully!');
        $this->command->info('👥 Total Customers: ' . Customer::count());
    }
}
