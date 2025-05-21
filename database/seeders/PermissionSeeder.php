<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions grouped by module
        $this->createDashboardPermissions();
        $this->createUserManagementPermissions();
        $this->createMasterDataPermissions();
        $this->createTransactionPermissions();
        $this->createFinancePermissions();
        $this->createInventoryPermissions();
        $this->createReportPermissions();

        // Create roles and assign permissions
        $this->createRoles();
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

    /**
     * Create Dashboard permissions
     */
    private function createDashboardPermissions()
    {
        $this->createPermissionIfNotExists('view dashboard');
    }

    /**
     * Create User Management permissions
     */
    private function createUserManagementPermissions()
    {
        $this->createPermissionIfNotExists('view users');
        $this->createPermissionIfNotExists('create users');
        $this->createPermissionIfNotExists('edit users');
        $this->createPermissionIfNotExists('delete users');
        $this->createPermissionIfNotExists('manage roles');
    }

    /**
     * Create Master Data permissions
     */
    private function createMasterDataPermissions()
    {
        $this->createPermissionIfNotExists('view master data');
        $this->createPermissionIfNotExists('manage customers');
        $this->createPermissionIfNotExists('manage suppliers');
        $this->createPermissionIfNotExists('manage barang');
        $this->createPermissionIfNotExists('manage kode barang');
        $this->createPermissionIfNotExists('manage categories');
        $this->createPermissionIfNotExists('manage stok owner');
        $this->createPermissionIfNotExists('manage perusahaan');
        $this->createPermissionIfNotExists('manage cara bayar');
    }

    /**
     * Create Transaction permissions
     */
    private function createTransactionPermissions()
    {
        $this->createPermissionIfNotExists('view transactions');
        $this->createPermissionIfNotExists('manage penjualan');
        $this->createPermissionIfNotExists('manage pembelian');
        $this->createPermissionIfNotExists('manage purchase orders');
        $this->createPermissionIfNotExists('manage surat jalan');
    }

    /**
     * Create Finance permissions
     */
    private function createFinancePermissions()
    {
        $this->createPermissionIfNotExists('view kas');
        $this->createPermissionIfNotExists('manage kas');
        $this->createPermissionIfNotExists('view hutang');
        $this->createPermissionIfNotExists('manage hutang');
    }

    /**
     * Create Inventory permissions
     */
    private function createInventoryPermissions()
    {
        $this->createPermissionIfNotExists('view stock');
        $this->createPermissionIfNotExists('manage stock');
        $this->createPermissionIfNotExists('manage stock adjustment');
        $this->createPermissionIfNotExists('manage panels');
    }

    /**
     * Create Report permissions
     */
    private function createReportPermissions()
    {
        $this->createPermissionIfNotExists('access sales report');
        $this->createPermissionIfNotExists('access purchase report');
        $this->createPermissionIfNotExists('access inventory report');
        $this->createPermissionIfNotExists('access finance report');
    }

    /**
     * Create roles and assign permissions
     */
    private function createRoles()
    {
        // Define role-based permissions as arrays for better organization
        $adminPermissions = Permission::all()->pluck('name')->toArray();
        
        $managerPermissions = [
            'view dashboard',
            'view users',
            'view master data',
            'manage customers',
            'manage suppliers',
            'view transactions',
            'view kas',
            'view hutang',
            'view stock',
            'access sales report',
            'access purchase report',
            'access inventory report',
            'access finance report'
        ];
        
        $salesPermissions = [
            'view dashboard',
            'view master data',
            'manage customers',
            'manage penjualan',
            'manage surat jalan',
            'view stock',
            'access sales report'
        ];
        
        $inventoryPermissions = [
            'view dashboard',
            'view master data',
            'manage barang',
            'manage kode barang',
            'view stock',
            'manage stock',
            'manage stock adjustment',
            'manage panels',
            'access inventory report'
        ];
        
        $financePermissions = [
            'view dashboard',
            'view master data',
            'view transactions',
            'view kas',
            'manage kas',
            'view hutang',
            'manage hutang',
            'access finance report'
        ];
        
        // Create or update roles with corresponding permissions
        $this->createOrUpdateRole('admin', $adminPermissions);
        $this->createOrUpdateRole('manager', $managerPermissions);
        $this->createOrUpdateRole('sales', $salesPermissions);
        $this->createOrUpdateRole('inventory', $inventoryPermissions);
        $this->createOrUpdateRole('finance', $financePermissions);
        
        // Create additional custom roles (first, second, third)
        $this->createOrUpdateRole('first', [
            'view dashboard',
            'view master data', 
            'manage penjualan',
            'manage customers',
            'view stock'
        ]);
        
        $this->createOrUpdateRole('second', [
            'view dashboard',
            'view master data',
            'view stock',
            'manage stock',
            'manage barang',
            'manage kode barang'
        ]);
        
        $this->createOrUpdateRole('third', [
            'view dashboard',
            'view kas',
            'manage kas',
            'view hutang',
            'manage hutang'
        ]);
    }
    
    /**
     * Create or update a role with permissions
     */
    private function createOrUpdateRole($name, $permissions)
    {
        // Check if role exists
        $role = Role::where('name', $name)->first();
        
        if (!$role) {
            // Create new role
            $role = Role::create(['name' => $name]);
            $this->command->info("Role '{$name}' created.");
        } else {
            $this->command->comment("Role '{$name}' already exists. Updating permissions.");
        }
        
        // Sync permissions
        $role->syncPermissions($permissions);
        $this->command->info("Permissions synced for role '{$name}'.");
    }
}