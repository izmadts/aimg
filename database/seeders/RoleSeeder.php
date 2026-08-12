<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full access to every module.', 'is_system' => true]
        );
        $admin->permissions()->sync(Permission::pluck('id'));

        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Manager', 'description' => 'Runs day-to-day operations across sales, purchases, cylinders and gas stock.', 'is_system' => true]
        );
        $manager->permissions()->sync(
            Permission::whereIn('module', ['sales', 'purchases', 'cylinders', 'gas_products', 'customers', 'suppliers', 'income_expense'])
                ->orWhereIn('slug', ['accounting.view', 'hrm.view'])
                ->pluck('id')
        );

        $accountant = Role::updateOrCreate(
            ['slug' => 'accountant'],
            ['name' => 'Accountant', 'description' => 'Manages the chart of accounts, income/expense entries and financial reports.', 'is_system' => true]
        );
        $accountant->permissions()->sync(
            Permission::whereIn('slug', [
                'accounting.view', 'accounting.manage',
                'income_expense.view', 'income_expense.create',
                'sales.view', 'purchases.view',
            ])->pluck('id')
        );

        $salesStaff = Role::updateOrCreate(
            ['slug' => 'sales-staff'],
            ['name' => 'Sales Staff', 'description' => 'Creates sales, manages customers and issues/sells cylinders.', 'is_system' => false]
        );
        $salesStaff->permissions()->sync(
            Permission::whereIn('slug', [
                'sales.view', 'sales.create', 'sales.edit',
                'customers.view', 'customers.create', 'customers.edit',
                'cylinders.view', 'gas_products.view',
            ])->pluck('id')
        );

        $warehouseStaff = Role::updateOrCreate(
            ['slug' => 'warehouse-staff'],
            ['name' => 'Warehouse Staff', 'description' => 'Maintains cylinder and gas stock records in the warehouse.', 'is_system' => false]
        );
        $warehouseStaff->permissions()->sync(
            Permission::whereIn('slug', [
                'cylinders.view', 'cylinders.create', 'cylinders.edit', 'cylinders.delete',
                'gas_products.view', 'gas_products.create', 'gas_products.edit',
                'purchases.view',
            ])->pluck('id')
        );

        $this->command->info('✅ Roles seeded successfully!');
        $this->command->info('👔 Total Roles: ' . Role::count());
    }
}
