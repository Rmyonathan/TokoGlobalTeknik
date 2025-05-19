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
     * Create Dashboard permissions
     */
    private function createDashboardPermissions()
    {
        Permission::create(['name' => 'view dashboard']);
    }

    /**
     * Create User Management permissions
     */
    private function createUserManagementPermissions()
    {
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'manage roles']);
    }

    /**
     * Create Master Data permissions
     */
    private function createMasterDataPermissions()
    {
        Permission::create(['name' => 'view master data']);
        Permission::create(['name' => 'manage customers']);
        Permission::create(['name' => 'manage suppliers']);
        Permission::create(['name' => 'manage barang']);
        Permission::create(['name' => 'manage kode barang']);
        Permission::create(['name' => 'manage categories']);
        Permission::create(['name' => 'manage stok owner']);
        Permission::create(['name' => 'manage perusahaan']);
        Permission::create(['name' => 'manage cara bayar']);
    }

    /**
     * Create Transaction permissions
     */
    private function createTransactionPermissions()
    {
        Permission::create(['name' => 'view transactions']);
        Permission::create(['name' => 'manage penjualan']);
        Permission::create(['name' => 'manage pembelian']);
        Permission::create(['name' => 'manage purchase orders']);
        Permission::create(['name' => 'manage surat jalan']);
    }

    /**
     * Create Finance permissions
     */
    private function createFinancePermissions()
    {
        Permission::create(['name' => 'view kas']);
        Permission::create(['name' => 'manage kas']);
        Permission::create(['name' => 'view hutang']);
        Permission::create(['name' => 'manage hutang']);
    }

    /**
     * Create Inventory permissions
     */
    private function createInventoryPermissions()
    {
        Permission::create(['name' => 'view stock']);
        Permission::create(['name' => 'manage stock']);
        Permission::create(['name' => 'manage stock adjustment']);
        Permission::create(['name' => 'manage panels']);
    }

    /**
     * Create Report permissions
     */
    private function createReportPermissions()
    {
        Permission::create(['name' => 'access sales report']);
        Permission::create(['name' => 'access purchase report']);
        Permission::create(['name' => 'access inventory report']);
        Permission::create(['name' => 'access finance report']);
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
        
        // Create roles with corresponding permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($adminPermissions);
        
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo($managerPermissions);
        
        $salesRole = Role::create(['name' => 'sales']);
        $salesRole->givePermissionTo($salesPermissions);
        
        $inventoryRole = Role::create(['name' => 'inventory']);
        $inventoryRole->givePermissionTo($inventoryPermissions);
        
        $financeRole = Role::create(['name' => 'finance']);
        $financeRole->givePermissionTo($financePermissions);
        
        // Create additional custom roles (first, second, third)
        $firstRole = Role::create(['name' => 'first']);
        $firstRole->givePermissionTo([
            'view dashboard',
            'view master data', 
            'manage penjualan',
            'manage customers',
            'view stock'
        ]);
        
        $secondRole = Role::create(['name' => 'second']);
        $secondRole->givePermissionTo([
            'view dashboard',
            'view master data',
            'view stock',
            'manage stock',
            'manage barang',
            'manage kode barang'
        ]);
        
        $thirdRole = Role::create(['name' => 'third']);
        $thirdRole->givePermissionTo([
            'view dashboard',
            'view kas',
            'manage kas',
            'view hutang',
            'manage hutang'
        ]);
    }
}