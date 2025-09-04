<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleGroup;
use App\Models\Role;

class RoleGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create role groups
        $roleGroups = [
            [
                'name' => 'management',
                'display_name' => 'Management',
                'description' => 'Role untuk level manajemen dan administrasi',
                'color' => '#dc3545',
                'icon' => 'fas fa-crown',
                'sort_order' => 1,
            ],
            [
                'name' => 'sales',
                'display_name' => 'Sales & Marketing',
                'description' => 'Role untuk tim sales dan marketing',
                'color' => '#28a745',
                'icon' => 'fas fa-chart-line',
                'sort_order' => 2,
            ],
            [
                'name' => 'inventory',
                'display_name' => 'Inventory & Warehouse',
                'description' => 'Role untuk manajemen inventory dan gudang',
                'color' => '#ffc107',
                'icon' => 'fas fa-boxes',
                'sort_order' => 3,
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance & Accounting',
                'description' => 'Role untuk keuangan dan akuntansi',
                'color' => '#17a2b8',
                'icon' => 'fas fa-calculator',
                'sort_order' => 4,
            ],
            [
                'name' => 'operations',
                'display_name' => 'Operations',
                'description' => 'Role untuk operasional harian',
                'color' => '#6f42c1',
                'icon' => 'fas fa-cogs',
                'sort_order' => 5,
            ],
            [
                'name' => 'custom',
                'display_name' => 'Custom Roles',
                'description' => 'Role khusus yang dibuat sesuai kebutuhan',
                'color' => '#6c757d',
                'icon' => 'fas fa-user-cog',
                'sort_order' => 6,
            ],
        ];

        foreach ($roleGroups as $groupData) {
            RoleGroup::updateOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );
        }

        // Assign existing roles to groups
        $this->assignRolesToGroups();
    }

    /**
     * Assign existing roles to appropriate groups
     */
    private function assignRolesToGroups(): void
    {
        $roleAssignments = [
            'management' => ['admin', 'manager'],
            'sales' => ['sales', 'senior_sales'],
            'inventory' => ['inventory', 'senior_inventory'],
            'finance' => ['finance'],
            'operations' => ['first', 'second', 'third'],
            'custom' => ['supervisor'],
        ];

        foreach ($roleAssignments as $groupName => $roleNames) {
            $group = RoleGroup::where('name', $groupName)->first();
            
            if ($group) {
                foreach ($roleNames as $roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $role->update([
                            'group_id' => $group->id,
                            'display_name' => $this->getRoleDisplayName($roleName),
                            'description' => $this->getRoleDescription($roleName),
                            'sort_order' => $this->getRoleSortOrder($roleName),
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get display name for role
     */
    private function getRoleDisplayName(string $roleName): string
    {
        $displayNames = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'sales' => 'Sales',
            'senior_sales' => 'Senior Sales',
            'inventory' => 'Inventory Staff',
            'senior_inventory' => 'Senior Inventory',
            'finance' => 'Finance Staff',
            'first' => 'First Level',
            'second' => 'Second Level',
            'third' => 'Third Level',
            'supervisor' => 'Supervisor',
        ];

        return $displayNames[$roleName] ?? ucfirst(str_replace('_', ' ', $roleName));
    }

    /**
     * Get description for role
     */
    private function getRoleDescription(string $roleName): string
    {
        $descriptions = [
            'admin' => 'Full access to all system features and settings',
            'manager' => 'Management level access with approval permissions',
            'sales' => 'Sales and customer management permissions',
            'senior_sales' => 'Senior sales with additional cancel permissions',
            'inventory' => 'Inventory and stock management permissions',
            'senior_inventory' => 'Senior inventory with additional cancel permissions',
            'finance' => 'Financial and accounting permissions',
            'first' => 'First level operational permissions',
            'second' => 'Second level operational permissions',
            'third' => 'Third level operational permissions',
            'supervisor' => 'Supervisory role with cancel permissions',
        ];

        return $descriptions[$roleName] ?? 'Custom role for specific needs';
    }

    /**
     * Get sort order for role
     */
    private function getRoleSortOrder(string $roleName): int
    {
        $sortOrders = [
            'admin' => 1,
            'manager' => 2,
            'senior_sales' => 3,
            'sales' => 4,
            'senior_inventory' => 5,
            'inventory' => 6,
            'finance' => 7,
            'supervisor' => 8,
            'first' => 9,
            'second' => 10,
            'third' => 11,
        ];

        return $sortOrders[$roleName] ?? 99;
    }
}