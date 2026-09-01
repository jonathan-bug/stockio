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
            'categories.index'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission
            ]);
        }

        // Role
        $role = Role::create([
            'name' => 'Administrator'
        ]);

        // Asign permission to role
        $permissions = Permission::whereIn('name', $permissions)->get();
        $role->givePermissionTo($permissions);

        // Asign role to user
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $user->assignRole($role);
    }
}
