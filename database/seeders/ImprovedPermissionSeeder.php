<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleGroup;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class ImprovedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions grouped by module
        $this->createPermissionsByModule();

        // Create role groups first
        $this->createRoleGroups();

        // Create roles and assign to groups
        $this->createRolesWithGroups();
    }

    /**
     * Create permissions organized by modules
     */
    private function createPermissionsByModule(): void
    {
        $permissionModules = [
            'Dashboard' => [
                'view dashboard',
            ],
            'User Management' => [
                'view users',
                'edit users',
                'manage users',
                'delete users',
                'manage roles',
                'edit roles',
                'delete roles',
            ],
            'Master Data' => [
                'view master data',
                'manage customers',
                'edit customers',
                'delete customers',
                'manage suppliers',
                'edit suppliers',
                'delete suppliers',
                'manage barang',
                'edit barang',
                'delete barang',
                'manage kode barang',
                'edit kode barang',
                'delete kode barang',
                'manage panels',
                'edit panels',
                'delete panels',
                'view wilayah',
                'manage wilayah',
                'edit wilayah',
            ],
            'Transactions' => [
                'view transactions',
                'manage penjualan',
                'edit penjualan',
                'cancel penjualan',
                'manage pembelian',
                'edit pembelian',
                'cancel pembelian',
                'manage surat jalan',
                'edit surat jalan',
                'cancel surat jalan',
            ],
            'Sales Order' => [
                'view sales order',
                'create sales order',
                'edit sales order',
                'manage sales order',
                'approve sales order',
                'cancel sales order',
            ],
            'Return Barang' => [
                'view return barang',
                'create return barang',
                'edit return barang',
                'manage return barang',
                'approve return barang',
                'reject return barang',
                'process return barang',
            ],
            'Finance' => [
                'view kas',
                'manage kas',
                'edit kas',
                'cancel kas',
                'view hutang',
                'manage hutang',
                'edit hutang',
                'cancel hutang',
                'view pembayaran piutang',
                'edit pembayaran piutang',
                'manage pembayaran piutang',
            ],
            'Inventory' => [
                'view stock',
                'manage stock',
                'manage stock adjustment',
                'edit stock adjustment',
                'cancel stock adjustment',
            ],
            'Reports' => [
                'view laporan',
                'access sales report',
                'access purchase report',
                'access inventory report',
                'access finance report',
            ],
        ];

        foreach ($permissionModules as $module => $permissions) {
            foreach ($permissions as $permission) {
                $this->createPermissionIfNotExists($permission);
            }
        }
    }

    /**
     * Create role groups
     */
    private function createRoleGroups(): void
    {
        $roleGroups = [
            [
                'name' => 'management',
                'display_name' => 'Management',
                'description' => 'Top-level management and administrative roles',
                'color' => '#dc3545',
                'icon' => 'fas fa-crown',
                'sort_order' => 1,
            ],
            [
                'name' => 'sales',
                'display_name' => 'Sales & Marketing',
                'description' => 'Sales team and marketing roles',
                'color' => '#28a745',
                'icon' => 'fas fa-chart-line',
                'sort_order' => 2,
            ],
            [
                'name' => 'inventory',
                'display_name' => 'Inventory & Warehouse',
                'description' => 'Inventory management and warehouse operations',
                'color' => '#ffc107',
                'icon' => 'fas fa-boxes',
                'sort_order' => 3,
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance & Accounting',
                'description' => 'Financial management and accounting roles',
                'color' => '#17a2b8',
                'icon' => 'fas fa-calculator',
                'sort_order' => 4,
            ],
            [
                'name' => 'operations',
                'display_name' => 'Operations',
                'description' => 'Daily operational roles',
                'color' => '#6f42c1',
                'icon' => 'fas fa-cogs',
                'sort_order' => 5,
            ],
            [
                'name' => 'custom',
                'display_name' => 'Custom Roles',
                'description' => 'Custom roles for specific needs',
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
    }

    /**
     * Create roles with proper grouping
     */
    private function createRolesWithGroups(): void
    {
        $roles = [
            // Management Group
            'admin' => [
                'group' => 'management',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => Permission::all()->pluck('name')->toArray(),
                'sort_order' => 1,
            ],
            'manager' => [
                'group' => 'management',
                'display_name' => 'Manager',
                'description' => 'Management level access with approval permissions',
                'permissions' => [
                    'view dashboard',
                    'view users',
                    'view master data',
                    'manage customers',
                    'edit customers',
                    'manage suppliers',
                    'edit suppliers',
                    'view transactions',
                    'edit penjualan',
                    'edit pembelian',
                    'view sales order',
                    'create sales order',
                    'edit sales order',
                    'manage sales order',
                    'view return barang',
                    'create return barang',
                    'edit return barang',
                    'manage return barang',
                    'approve return barang',
                    'reject return barang',
                    'process return barang',
                    'view kas',
                    'edit kas',
                    'view hutang',
                    'edit hutang',
                    'view stock',
                    'view laporan',
                    'view pembayaran piutang',
                    'view wilayah',
                    'manage wilayah',
                    'edit wilayah',
                    'access sales report',
                    'access purchase report',
                    'access inventory report',
                    'access finance report',
                ],
                'sort_order' => 2,
            ],

            // Sales Group
            'sales' => [
                'group' => 'sales',
                'display_name' => 'Sales',
                'description' => 'Sales team member with customer management',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'manage customers',
                    'edit customers',
                    'manage penjualan',
                    'edit penjualan',
                    'manage surat jalan',
                    'edit surat jalan',
                    'view sales order',
                    'create sales order',
                    'edit sales order',
                    'view return barang',
                    'create return barang',
                    'view stock',
                    'view pembayaran piutang',
                    'view laporan',
                    'access sales report',
                ],
                'sort_order' => 3,
            ],
            'senior_sales' => [
                'group' => 'sales',
                'display_name' => 'Senior Sales',
                'description' => 'Senior sales with additional cancel permissions',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'manage customers',
                    'edit customers',
                    'delete customers',
                    'manage penjualan',
                    'edit penjualan',
                    'cancel penjualan',
                    'manage surat jalan',
                    'edit surat jalan',
                    'cancel surat jalan',
                    'view sales order',
                    'create sales order',
                    'edit sales order',
                    'manage sales order',
                    'view stock',
                    'view pembayaran piutang',
                    'view laporan',
                    'access sales report',
                ],
                'sort_order' => 4,
            ],

            // Inventory Group
            'inventory' => [
                'group' => 'inventory',
                'display_name' => 'Inventory Staff',
                'description' => 'Inventory management and stock operations',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'manage barang',
                    'edit barang',
                    'manage kode barang',
                    'edit kode barang',
                    'view stock',
                    'manage stock',
                    'manage stock adjustment',
                    'edit stock adjustment',
                    'manage panels',
                    'edit panels',
                    'view laporan',
                    'access inventory report',
                ],
                'sort_order' => 5,
            ],
            'senior_inventory' => [
                'group' => 'inventory',
                'display_name' => 'Senior Inventory',
                'description' => 'Senior inventory with additional cancel permissions',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'manage barang',
                    'edit barang',
                    'delete barang',
                    'manage kode barang',
                    'edit kode barang',
                    'delete kode barang',
                    'view stock',
                    'manage stock',
                    'manage stock adjustment',
                    'edit stock adjustment',
                    'cancel stock adjustment',
                    'manage panels',
                    'edit panels',
                    'delete panels',
                    'view laporan',
                    'access inventory report',
                ],
                'sort_order' => 6,
            ],

            // Finance Group
            'finance' => [
                'group' => 'finance',
                'display_name' => 'Finance Staff',
                'description' => 'Financial management and accounting',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'view transactions',
                    'view kas',
                    'manage kas',
                    'edit kas',
                    'cancel kas',
                    'view hutang',
                    'manage hutang',
                    'edit hutang',
                    'cancel hutang',
                    'view pembayaran piutang',
                    'edit pembayaran piutang',
                    'manage pembayaran piutang',
                    'view laporan',
                    'access finance report',
                ],
                'sort_order' => 7,
            ],

            // Operations Group
            'first' => [
                'group' => 'operations',
                'display_name' => 'First Level',
                'description' => 'First level operational permissions',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'manage penjualan',
                    'edit penjualan',
                    'manage customers',
                    'edit customers',
                    'view stock',
                    'view pembayaran piutang',
                    'view laporan',
                    'view wilayah',
                    'manage wilayah',
                    'edit wilayah',
                ],
                'sort_order' => 8,
            ],
            'second' => [
                'group' => 'operations',
                'display_name' => 'Second Level',
                'description' => 'Second level operational permissions',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'view stock',
                    'manage stock',
                    'manage barang',
                    'edit barang',
                    'manage kode barang',
                    'edit kode barang',
                    'view laporan',
                    'view wilayah',
                ],
                'sort_order' => 9,
            ],
            'third' => [
                'group' => 'operations',
                'display_name' => 'Third Level',
                'description' => 'Third level operational permissions',
                'permissions' => [
                    'view dashboard',
                    'view kas',
                    'manage kas',
                    'edit kas',
                    'view hutang',
                    'manage hutang',
                    'edit hutang',
                    'view laporan',
                ],
                'sort_order' => 10,
            ],

            // Custom Group
            'supervisor' => [
                'group' => 'custom',
                'display_name' => 'Supervisor',
                'description' => 'Supervisory role with cancel permissions',
                'permissions' => [
                    'view dashboard',
                    'view master data',
                    'view transactions',
                    'edit penjualan',
                    'cancel penjualan',
                    'edit pembelian',
                    'cancel pembelian',
                    'view sales order',
                    'edit sales order',
                    'cancel sales order',
                    'view stock',
                    'view laporan',
                ],
                'sort_order' => 11,
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $group = RoleGroup::where('name', $roleData['group'])->first();
            
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'group_id' => $group ? $group->id : null,
                    'sort_order' => $roleData['sort_order'],
                    'is_active' => true,
                ]
            );

            // Assign permissions
            $role->syncPermissions($roleData['permissions']);
        }
    }

    /**
     * Create permission if it doesn't exist
     */
    private function createPermissionIfNotExists($name)
    {
        if (!Permission::where('name', $name)->exists()) {
            Permission::create(['name' => $name]);
            $this->command->info("Permission '{$name}' created.");
        } else {
            $this->command->comment("Permission '{$name}' already exists. Skipped.");
        }
    }
}
