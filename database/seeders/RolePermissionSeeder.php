<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permission
        $permissions = [
            // Users
            'users.index',
            'users.create',
            'users.edit',
            'users.activate',
            'users.deactivate',

            // Roles
            'roles.index',
            'roles.create',
            'roles.edit',
            'roles.activate',
            'roles.deactivate',
            'roles.permissions',

            // Categories
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.activate',
            'categories.deactivate',

            // Products
            'products.index',
            'products.create',
            'products.edit',
            'products.activate',
            'products.deactivate',

            // Suppliers
            'suppliers.index',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.activate',
            'suppliers.deactivate',

            // Purchases
            'purchases.index',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',

            // Stock
            'stock.index',

            // Kardex
            'kardex.index',

            // Sales
            'sales.index',
            'sales.create',
            'sales.show',

            // Inventory adjustments
            'inventory_adjustments.index'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }

        // Role
        $role = Role::firstOrCreate([
            'name' => 'Administrator'
        ]);

        // Asign permission to role
        $role->syncPermissions($permissions);

        // Asign role to user
        $user = User::where('email', 'test@example.com')->firstOrFail();

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
