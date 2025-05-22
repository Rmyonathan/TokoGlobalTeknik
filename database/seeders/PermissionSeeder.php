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
        $this->createPermissionIfNotExists('edit customers');
        $this->createPermissionIfNotExists('delete customers');
        $this->createPermissionIfNotExists('manage suppliers');
        $this->createPermissionIfNotExists('edit suppliers');
        $this->createPermissionIfNotExists('delete suppliers');
        $this->createPermissionIfNotExists('manage barang');
        $this->createPermissionIfNotExists('edit barang');
        $this->createPermissionIfNotExists('delete barang');
        $this->createPermissionIfNotExists('manage kode barang');
        $this->createPermissionIfNotExists('edit kode barang');
        $this->createPermissionIfNotExists('delete kode barang');
        $this->createPermissionIfNotExists('manage categories');
        $this->createPermissionIfNotExists('edit categories');
        $this->createPermissionIfNotExists('delete categories');
        $this->createPermissionIfNotExists('manage stok owner');
        $this->createPermissionIfNotExists('delete stok owner');
        $this->createPermissionIfNotExists('manage perusahaan');
        $this->createPermissionIfNotExists('edit perusahaan');
        $this->createPermissionIfNotExists('delete perusahaan');
        $this->createPermissionIfNotExists('manage cara bayar');
        $this->createPermissionIfNotExists('delete cara bayar');
    }

    /**
     * Create Transaction permissions
     */
    private function createTransactionPermissions()
    {
        $this->createPermissionIfNotExists('view transactions');
        $this->createPermissionIfNotExists('manage penjualan');
        $this->createPermissionIfNotExists('edit penjualan');
        $this->createPermissionIfNotExists('cancel penjualan');
        $this->createPermissionIfNotExists('delete penjualan');
        $this->createPermissionIfNotExists('manage pembelian');
        $this->createPermissionIfNotExists('edit pembelian');
        $this->createPermissionIfNotExists('cancel pembelian');
        $this->createPermissionIfNotExists('delete pembelian');
        $this->createPermissionIfNotExists('manage purchase orders');
        $this->createPermissionIfNotExists('edit purchase orders');
        $this->createPermissionIfNotExists('cancel purchase orders');
        $this->createPermissionIfNotExists('delete purchase orders');
        $this->createPermissionIfNotExists('manage surat jalan');
        $this->createPermissionIfNotExists('edit surat jalan');
        $this->createPermissionIfNotExists('cancel surat jalan');
        $this->createPermissionIfNotExists('delete surat jalan');
    }

    /**
     * Create Finance permissions
     */
    private function createFinancePermissions()
    {
        $this->createPermissionIfNotExists('view kas');
        $this->createPermissionIfNotExists('manage kas');
        $this->createPermissionIfNotExists('edit kas');
        $this->createPermissionIfNotExists('cancel kas');
        $this->createPermissionIfNotExists('delete kas');
        $this->createPermissionIfNotExists('view hutang');
        $this->createPermissionIfNotExists('manage hutang');
        $this->createPermissionIfNotExists('edit hutang');
        $this->createPermissionIfNotExists('cancel hutang');
        $this->createPermissionIfNotExists('delete hutang');
    }

    /**
     * Create Inventory permissions
     */
    private function createInventoryPermissions()
    {
        $this->createPermissionIfNotExists('view stock');
        $this->createPermissionIfNotExists('manage stock');
        $this->createPermissionIfNotExists('manage stock adjustment');
        $this->createPermissionIfNotExists('edit stock adjustment');
        $this->createPermissionIfNotExists('cancel stock adjustment');
        $this->createPermissionIfNotExists('delete stock adjustment');
        $this->createPermissionIfNotExists('manage panels');
        $this->createPermissionIfNotExists('edit panels');
        $this->createPermissionIfNotExists('delete panels');
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
            'edit customers',
            'manage suppliers',
            'edit suppliers',
            'view transactions',
            'edit penjualan',
            'edit pembelian',
            'view kas',
            'edit kas',
            'view hutang',
            'edit hutang',
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
            'edit customers',
            'manage penjualan',
            'edit penjualan',
            'manage surat jalan',
            'edit surat jalan',
            'view stock',
            'access sales report'
        ];
        
        $inventoryPermissions = [
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
            'access inventory report'
        ];
        
        $financePermissions = [
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
            'access finance report'
        ];
        
        // Senior roles with cancel permissions
        $seniorSalesPermissions = [
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
            'view stock',
            'access sales report'
        ];
        
        $seniorInventoryPermissions = [
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
            'access inventory report'
        ];
        
        // Create or update roles with corresponding permissions
        $this->createOrUpdateRole('admin', $adminPermissions);
        $this->createOrUpdateRole('manager', $managerPermissions);
        $this->createOrUpdateRole('sales', $salesPermissions);
        $this->createOrUpdateRole('senior_sales', $seniorSalesPermissions);
        $this->createOrUpdateRole('inventory', $inventoryPermissions);
        $this->createOrUpdateRole('senior_inventory', $seniorInventoryPermissions);
        $this->createOrUpdateRole('finance', $financePermissions);
        
        // Create additional custom roles (first, second, third) with updated permissions
        $this->createOrUpdateRole('first', [
            'view dashboard',
            'view master data', 
            'manage penjualan',
            'edit penjualan',
            'manage customers',
            'edit customers',
            'view stock'
        ]);
        
        $this->createOrUpdateRole('second', [
            'view dashboard',
            'view master data',
            'view stock',
            'manage stock',
            'manage barang',
            'edit barang',
            'manage kode barang',
            'edit kode barang'
        ]);
        
        $this->createOrUpdateRole('third', [
            'view dashboard',
            'view kas',
            'manage kas',
            'edit kas',
            'view hutang',
            'manage hutang',
            'edit hutang'
        ]);
        
        // Supervisor role with cancel permissions
        $this->createOrUpdateRole('supervisor', [
            'view dashboard',
            'view master data',
            'view transactions',
            'edit penjualan',
            'cancel penjualan',
            'edit pembelian',
            'cancel pembelian',
            'view kas',
            'edit kas',
            'cancel kas',
            'view stock',
            'edit stock adjustment',
            'cancel stock adjustment'
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