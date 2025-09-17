<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AssignStockTransferPermission extends Command
{
    protected $signature = 'permission:assign-stock-transfer {user_id?}';
    protected $description = 'Assign stock transfer permissions to user or role';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            // Assign to specific user
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return;
            }
            
            $permissions = [
                'view stock transfer',
                'create stock transfer',
                'approve stock transfer',
                'cancel stock transfer',
                'manage global stock',
            ];
            
            $user->givePermissionTo($permissions);
            $this->info("Permissions assigned to user: {$user->name}");
            
        } else {
            // Run the seeder to assign permissions to all roles
            $this->call('db:seed', ['--class' => 'StockTransferPermissionSeeder']);
            $this->info("Stock transfer permissions assigned to all roles");
        }
    }
}