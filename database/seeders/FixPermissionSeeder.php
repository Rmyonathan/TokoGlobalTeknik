<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class FixPermissionsSeeder extends Seeder
{
    /**
     * Fix permissions and roles.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Display existing permissions
        $this->command->info('--- Current Permissions ---');
        $existingPermissions = Permission::all();
        foreach ($existingPermissions as $permission) {
            $this->command->info("- {$permission->name} (ID: {$permission->id})");
        }

        // Display existing roles
        $this->command->info('--- Current Roles ---');
        $existingRoles = Role::all();
        foreach ($existingRoles as $role) {
            $permissions = $role->permissions->pluck('name')->toArray();
            $this->command->info("- {$role->name} (ID: {$role->id}) - Permissions: " . implode(', ', $permissions));
        }

        // Display users and their roles
        $this->command->info('--- Users and Roles ---');
        $users = User::all();
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->toArray();
            $permissions = $user->permissions->pluck('name')->toArray();
            $this->command->info("- {$user->name} (ID: {$user->id}, Role: {$user->role})");
            $this->command->info("  Spatie Roles: " . implode(', ', $roles));
            $this->command->info("  Direct Permissions: " . implode(', ', $permissions));
        }

        // Check for admin users
        $this->command->info('--- Admin Users Check ---');
        $adminUsers = User::where('role', 'admin')->get();
        if ($adminUsers->isEmpty()) {
            $this->command->warn('No users with role "admin" found in users table!');
        } else {
            $this->command->info('Admin users found: ' . $adminUsers->pluck('name')->implode(', '));
            
            // Ensure admin users have the admin role
            foreach ($adminUsers as $adminUser) {
                if (!$adminUser->hasRole('admin')) {
                    $adminUser->assignRole('admin');
                    $this->command->info("Assigned 'admin' role to user {$adminUser->name}");
                } else {
                    $this->command->info("User {$adminUser->name} already has 'admin' role");
                }
            }
        }

        // Check if admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->warn('Admin role does not exist! Creating it...');
            $adminRole = Role::create(['name' => 'admin']);
            $adminRole->givePermissionTo(Permission::all());
            $this->command->info('Admin role created with all permissions.');
        } else {
            // Ensure admin role has all permissions
            $adminRole->syncPermissions(Permission::all());
            $this->command->info('Admin role synced with all permissions.');
        }

        // Make sure critical paths have required permissions
        $criticalPermissions = [
            'view dashboard',
            'view kas',
            'manage kas',
            'edit users',
            'view users',
            'view master data',
        ];

        $this->command->info('--- Critical Permissions Check ---');
        foreach ($criticalPermissions as $permName) {
            if (!Permission::where('name', $permName)->exists()) {
                Permission::create(['name' => $permName]);
                $this->command->info("Created missing critical permission: {$permName}");
            } else {
                $this->command->info("Critical permission {$permName} exists");
            }
        }

        // Assign all critical permissions to admin role
        $adminRole->givePermissionTo($criticalPermissions);
        $this->command->info('Critical permissions assigned to admin role.');

        // Clear all caches
        $this->command->call('cache:clear');
        $this->command->call('config:clear');
        $this->command->call('route:clear');
        $this->command->call('view:clear');
        $this->command->info('All caches cleared.');
    }
}