<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupPermissionsSeeder extends Seeder
{
    /**
     * Clean up permissions tables.
     *
     * @return void
     */
    public function run()
    {
        // Ask for confirmation before proceeding
        if (!$this->command->confirm('This will delete ALL roles and permissions. Continue?', false)) {
            $this->command->info('Operation cancelled.');
            return;
        }

        // Force truncate each table
        $this->truncateTable('role_has_permissions');
        $this->truncateTable('model_has_roles');
        $this->truncateTable('model_has_permissions');
        $this->truncateTable('roles');
        $this->truncateTable('permissions');

        $this->command->info('All roles and permissions have been deleted.');
        $this->command->info('You should now run: php artisan db:seed --class=PermissionSeeder');
    }

    /**
     * Truncate the specified table.
     *
     * @param string $table
     * @return void
     */
    private function truncateTable($table)
    {
        if (Schema::hasTable($table)) {
            // Disable foreign key checks to allow truncating
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($table)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->command->info("Table '{$table}' truncated.");
        } else {
            $this->command->warn("Table '{$table}' does not exist. Skipped.");
        }
    }
}