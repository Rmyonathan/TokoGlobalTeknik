Permission System Documentation
This document outlines the permission system used in the application, based on Spatie's Laravel Permission package.
Permission Structure
Permissions are organized by module to make management easier:
Dashboard

view dashboard: Access to view the main dashboard

User Management

view users: View list of users
create users: Create new users
edit users: Edit existing users
delete users: Delete users
manage roles: Create, edit, and assign roles

Master Data

view master data: View all master data
manage customers: Create, edit, delete customers
manage suppliers: Create, edit, delete suppliers
manage barang: Manage products/items
manage kode barang: Manage product codes
manage categories: Manage product categories
manage stok owner: Manage stock owners
manage perusahaan: Manage company information
manage cara bayar: Manage payment methods

Transactions

view transactions: View all transactions
manage penjualan: Create and manage sales transactions
manage pembelian: Create and manage purchase transactions
manage purchase orders: Manage purchase orders
manage surat jalan: Create and manage delivery notes

Finance

view kas: View cash/financial information
manage kas: Manage cash transactions
view hutang: View debts
manage hutang: Manage debts and payments

Inventory

view stock: View inventory levels
manage stock: Update inventory
manage stock adjustment: Perform stock adjustments
manage panels: Manage panel inventory

Reports

access sales report: View sales reports
access purchase report: View purchase reports
access inventory report: View inventory reports
access finance report: View financial reports

Predefined Roles
The system comes with the following predefined roles, each with specific permissions:
Admin

Has all permissions in the system

Manager

View-oriented permissions for most areas
Limited editing capabilities
Access to all reports

Sales

Focus on customer management and sales
Access to sales reports

Inventory

Focus on inventory and product management
Access to inventory reports

Finance

Focus on financial aspects
Access to financial reports

Custom Roles

first: Focused on sales and customer management
second: Focused on inventory management
third: Focused on financial management

Adding New Permissions
To add new permissions:

Add the permission to the appropriate method in PermissionSeeder.php
Add the permission to the relevant roles in the createRoles() method
Run php artisan db:seed --class=PermissionSeeder to create the new permission
Add middleware checks to the relevant routes

Checking Permissions in Code
In Routes
phpRoute::group(['middleware' => ['permission:view dashboard']], function () {
    // Routes that require the 'view dashboard' permission
});
In Controllers
phppublic function update(Request $request, $id)
{
    if (!auth()->user()->can('edit users')) {
        abort(403);
    }
    
    // Method implementation
}
In Blade Templates
blade@can('edit users')
    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit</a>
@endcan
User Assignment
When creating a new user, assign them to a role:
php
$user = User::create([
    'name' => 'New User',
    'email' => 'user@example.com',
    'password' => Hash::make('password'),
]);

$user->assignRole('sales');