<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Every permission an admin can hand out to a role. Grouped by module so
     * the role-editing UI can render a checkbox matrix.
     */
    public function run(): void
    {
        $modules = [
            'sales' => 'Sales',
            'purchases' => 'Purchases',
            'cylinders' => 'Cylinders',
            'gas_products' => 'Gas Products',
            'customers' => 'Customers',
            'suppliers' => 'Suppliers',
            'income_expense' => 'Income & Expense',
        ];

        foreach ($modules as $slug => $label) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::updateOrCreate(
                    ['slug' => "{$slug}.{$action}"],
                    [
                        'name' => ucfirst($action) . ' ' . $label,
                        'module' => $slug,
                        'description' => ucfirst($action) . ' access for ' . $label,
                    ]
                );
            }
        }

        $extra = [
            ['slug' => 'accounting.view', 'name' => 'View Accounting Reports', 'module' => 'accounting', 'description' => 'View trial balance, income statement, balance sheet'],
            ['slug' => 'accounting.manage', 'name' => 'Manage Chart of Accounts', 'module' => 'accounting', 'description' => 'Create/edit/delete accounts'],
            ['slug' => 'hrm.view', 'name' => 'View HRM', 'module' => 'hrm', 'description' => 'View employees, attendance, leaves'],
            ['slug' => 'hrm.manage', 'name' => 'Manage HRM', 'module' => 'hrm', 'description' => 'Manage employees, salaries, attendance, advances, leaves'],
            ['slug' => 'users.manage', 'name' => 'Manage Users', 'module' => 'users', 'description' => 'Create/edit users and assign roles'],
            ['slug' => 'roles.manage', 'name' => 'Manage Roles & Permissions', 'module' => 'roles', 'description' => 'Create/edit roles and set their permissions'],
            ['slug' => 'settings.manage', 'name' => 'Manage System Settings', 'module' => 'settings', 'description' => 'Change system-wide settings such as public registration'],
            ['slug' => 'units.manage', 'name' => 'Manage Units', 'module' => 'units', 'description' => 'Add/edit/delete units of measure used by gas products'],
            ['slug' => 'cylinder_types.manage', 'name' => 'Manage Cylinder Types', 'module' => 'cylinder_types', 'description' => 'Add/edit/delete cylinder sizes/types'],
        ];

        foreach ($extra as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $this->command->info('✅ Permissions seeded successfully!');
        $this->command->info('🔑 Total Permissions: ' . Permission::count());
    }
}
