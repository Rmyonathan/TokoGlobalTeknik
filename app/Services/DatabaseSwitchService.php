<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class DatabaseSwitchService
{
    const SESSION_KEY = 'current_database';
    const CACHE_KEY = 'database_switch_info';
    const CACHE_TTL = 60; // 1 minute

    /**
     * Get available databases configuration
     */
    public function getAvailableDatabases(): array
    {
        return Config::get('database.available_databases', []);
    }

    /**
     * Get current database from session
     */
    public function getCurrentDatabase(): string
    {
        return Session::get(self::SESSION_KEY, 'primary');
    }

    /**
     * Set current database
     */
    public function setCurrentDatabase(string $databaseKey): bool
    {
        $availableDatabases = $this->getAvailableDatabases();
        
        if (!isset($availableDatabases[$databaseKey])) {
            return false;
        }

        Session::put(self::SESSION_KEY, $databaseKey);
        
        // Clear cache when switching database
        Cache::forget(self::CACHE_KEY);
        
        return true;
    }

    /**
     * Get current database connection name
     */
    public function getCurrentConnection(): string
    {
        $currentDb = $this->getCurrentDatabase();
        $availableDatabases = $this->getAvailableDatabases();
        
        return $availableDatabases[$currentDb]['connection'] ?? 'mysql';
    }

    /**
     * Switch to specific database
     */
    public function switchTo(string $databaseKey): bool
    {
        if (!$this->setCurrentDatabase($databaseKey)) {
            return false;
        }

        // Set the default connection for this request
        $connection = $this->getCurrentConnection();
        Config::set('database.default', $connection);
        
        return true;
    }

    /**
     * Get database info for current selection
     */
    public function getCurrentDatabaseInfo(): array
    {
        $currentDb = $this->getCurrentDatabase();
        $availableDatabases = $this->getAvailableDatabases();
        
        return $availableDatabases[$currentDb] ?? [];
    }

    /**
     * Test database connection
     */
    public function testConnection(string $databaseKey): bool
    {
        $availableDatabases = $this->getAvailableDatabases();
        
        if (!isset($availableDatabases[$databaseKey])) {
            return false;
        }

        $connection = $availableDatabases[$databaseKey]['connection'];
        
        try {
            DB::connection($connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database status for all available databases
     */
    public function getDatabaseStatus(): array
    {
        $availableDatabases = $this->getAvailableDatabases();
        $status = [];
        
        foreach ($availableDatabases as $key => $config) {
            $status[$key] = [
                'name' => $config['name'],
                'description' => $config['description'],
                'connection' => $config['connection'],
                'is_connected' => $this->testConnection($key),
                'is_current' => $key === $this->getCurrentDatabase(),
            ];
        }
        
        return $status;
    }

    /**
     * Get table count for current database
     */
    public function getTableCount(): int
    {
        try {
            $connection = $this->getCurrentConnection();
            $tables = DB::connection($connection)->select('SHOW TABLES');
            return count($tables);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get database size info
     */
    public function getDatabaseSize(): array
    {
        try {
            $connection = $this->getCurrentConnection();
            $result = DB::connection($connection)->select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    COUNT(*) AS table_count
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            return [
                'size_mb' => $result[0]->size_mb ?? 0,
                'table_count' => $result[0]->table_count ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'size_mb' => 0,
                'table_count' => 0,
            ];
        }
    }

    /**
     * Reset to default database
     */
    public function resetToDefault(): bool
    {
        return $this->switchTo('primary');
    }

    /**
     * Get database switch history (if needed)
     */
    public function getSwitchHistory(): array
    {
        return Cache::get('database_switch_history', []);
    }

    /**
     * Log database switch
     */
    public function logSwitch(string $from, string $to, ?string $userId = null): void
    {
        $history = $this->getSwitchHistory();
        $history[] = [
            'from' => $from,
            'to' => $to,
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        // Keep only last 50 switches
        $history = array_slice($history, -50);
        
        Cache::put('database_switch_history', $history, 3600); // 1 hour
    }

}
