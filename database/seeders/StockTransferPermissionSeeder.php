<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StockTransferPermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions for stock transfer
        $permissions = [
            'manage stock transfer',
            'view stock transfer',
            'create stock transfer',
            'edit stock transfer',
            'delete stock transfer',
            'approve stock transfer',
            'cancel stock transfer',
            'manage global stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles based on existing role structure
        $roles = [
            'admin' => [
                'manage stock transfer',
                'view stock transfer',
                'create stock transfer',
                'edit stock transfer',
                'delete stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
                'manage global stock',
            ],
            'manager' => [
                'manage stock transfer',
                'view stock transfer',
                'create stock transfer',
                'edit stock transfer',
                'delete stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
                'manage global stock',
            ],
            'inventory' => [
                'manage stock transfer',
                'view stock transfer',
                'create stock transfer',
                'edit stock transfer',
                'delete stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
                'manage global stock',
            ],
            'senior_inventory' => [
                'manage stock transfer',
                'view stock transfer',
                'create stock transfer',
                'edit stock transfer',
                'delete stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
                'manage global stock',
            ],
            'sales' => [
                'view stock transfer',
                'create stock transfer',
            ],
            'senior_sales' => [
                'view stock transfer',
                'create stock transfer',
            ],
            'first' => [
                'view stock transfer',
                'create stock transfer',
            ],
            'second' => [
                'view stock transfer',
                'create stock transfer',
            ],
            'third' => [
                'view stock transfer',
                'create stock transfer',
            ],
            'supervisor' => [
                'view stock transfer',
                'create stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }
    }
}