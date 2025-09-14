<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\DatabaseSwitchService;

class SyncDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:sync {--from=primary : Source database} {--to=secondary : Target database} {--tables= : Specific tables to sync (comma separated)} {--force : Force sync without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data between databases';

    protected $databaseSwitchService;

    public function __construct(DatabaseSwitchService $databaseSwitchService)
    {
        parent::__construct();
        $this->databaseSwitchService = $databaseSwitchService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromDb = $this->option('from');
        $toDb = $this->option('to');
        $tables = $this->option('tables');
        $force = $this->option('force');

        // Validate database connections
        if (!$this->databaseSwitchService->testConnection($fromDb)) {
            $this->error("Source database '{$fromDb}' is not connected!");
            return 1;
        }

        if (!$this->databaseSwitchService->testConnection($toDb)) {
            $this->error("Target database '{$toDb}' is not connected!");
            return 1;
        }

        // Get available databases
        $availableDatabases = $this->databaseSwitchService->getAvailableDatabases();
        
        if (!isset($availableDatabases[$fromDb]) || !isset($availableDatabases[$toDb])) {
            $this->error("Invalid database names. Available: " . implode(', ', array_keys($availableDatabases)));
            return 1;
        }

        $fromConnection = $availableDatabases[$fromDb]['connection'];
        $toConnection = $availableDatabases[$toDb]['connection'];

        // Get tables to sync
        $tablesToSync = $this->getTablesToSync($fromConnection, $tables);

        if (empty($tablesToSync)) {
            $this->error('No tables found to sync!');
            return 1;
        }

        // Show confirmation
        if (!$force) {
            $this->info("Will sync the following tables from '{$fromDb}' to '{$toDb}':");
            foreach ($tablesToSync as $table) {
                $this->line("  - {$table}");
            }
            
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Sync cancelled.');
                return 0;
            }
        }

        // Start syncing
        $this->info("Starting sync from '{$fromDb}' to '{$toDb}'...");
        
        $successCount = 0;
        $errorCount = 0;

        foreach ($tablesToSync as $table) {
            try {
                $this->syncTable($fromConnection, $toConnection, $table);
                $this->info("✓ Synced table: {$table}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to sync table '{$table}': " . $e->getMessage());
                $errorCount++;
            }
        }

        // Summary
        $this->info("\nSync completed!");
        $this->info("Successfully synced: {$successCount} tables");
        if ($errorCount > 0) {
            $this->warn("Failed to sync: {$errorCount} tables");
        }

        return 0;
    }

    /**
     * Get tables to sync
     */
    private function getTablesToSync(string $fromConnection, ?string $tables): array
    {
        if ($tables) {
            return array_map('trim', explode(',', $tables));
        }

        // Get all tables from source database
        $tables = DB::connection($fromConnection)->select('SHOW TABLES');
        $tableNames = [];
        
        foreach ($tables as $table) {
            $tableArray = (array) $table;
            $tableNames[] = reset($tableArray);
        }

        return $tableNames;
    }

    /**
     * Sync a single table
     */
    private function syncTable(string $fromConnection, string $toConnection, string $table): void
    {
        // Check if table exists in target database
        if (!Schema::connection($toConnection)->hasTable($table)) {
            // Create table structure
            $this->createTableStructure($fromConnection, $toConnection, $table);
        }

        // Clear target table
        DB::connection($toConnection)->table($table)->truncate();

        // Get data from source table
        $data = DB::connection($fromConnection)->table($table)->get();

        if ($data->isEmpty()) {
            return;
        }

        // Insert data in chunks
        $chunks = $data->chunk(1000);
        
        foreach ($chunks as $chunk) {
            DB::connection($toConnection)->table($table)->insert($chunk->toArray());
        }
    }

    /**
     * Create table structure in target database
     */
    private function createTableStructure(string $fromConnection, string $toConnection, string $table): void
    {
        // Get CREATE TABLE statement
        $createTable = DB::connection($fromConnection)->select("SHOW CREATE TABLE `{$table}`");
        $createStatement = $createTable[0]->{'Create Table'};

        // Execute CREATE TABLE in target database
        DB::connection($toConnection)->statement($createStatement);
    }
}
