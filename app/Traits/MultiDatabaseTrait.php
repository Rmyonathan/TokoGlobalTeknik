<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use App\Services\DatabaseSwitchService;

trait MultiDatabaseTrait
{
    /**
     * Get the current database connection
     */
    public function getCurrentConnection(): string
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        return $databaseSwitchService->getCurrentConnection();
    }

    /**
     * Set the database connection for this model
     */
    public function setConnection($connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get model using specific database connection
     */
    public static function onDatabase(string $databaseKey): self
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        $availableDatabases = $databaseSwitchService->getAvailableDatabases();
        
        if (!isset($availableDatabases[$databaseKey])) {
            throw new \InvalidArgumentException("Database '{$databaseKey}' not found in available databases");
        }

        $connection = $availableDatabases[$databaseKey]['connection'];
        
        return (new static)->setConnection($connection);
    }

    /**
     * Get all records from all databases
     */
    public static function fromAllDatabases(): \Illuminate\Support\Collection
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        $availableDatabases = $databaseSwitchService->getAvailableDatabases();
        $results = collect();

        foreach ($availableDatabases as $key => $config) {
            if ($databaseSwitchService->testConnection($key)) {
                try {
                    $records = static::onDatabase($key)->get();
                    $records->each(function ($record) use ($key) {
                        $record->database_source = $key;
                    });
                    $results = $results->merge($records);
                } catch (\Exception $e) {
                    // Skip if connection fails
                    continue;
                }
            }
        }

        return $results;
    }

    /**
     * Get records from specific databases
     */
    public static function fromDatabases(array $databaseKeys): \Illuminate\Support\Collection
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        $availableDatabases = $databaseSwitchService->getAvailableDatabases();
        $results = collect();

        foreach ($databaseKeys as $key) {
            if (isset($availableDatabases[$key]) && $databaseSwitchService->testConnection($key)) {
                try {
                    $records = static::onDatabase($key)->get();
                    $records->each(function ($record) use ($key) {
                        $record->database_source = $key;
                    });
                    $results = $results->merge($records);
                } catch (\Exception $e) {
                    // Skip if connection fails
                    continue;
                }
            }
        }

        return $results;
    }

    /**
     * Sync this model to all databases
     */
    public function syncToAllDatabases(): bool
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        $availableDatabases = $databaseSwitchService->getAvailableDatabases();
        $success = true;

        foreach ($availableDatabases as $key => $config) {
            if ($databaseSwitchService->testConnection($key)) {
                try {
                    $this->syncToDatabase($key);
                } catch (\Exception $e) {
                    $success = false;
                    \Log::error("Failed to sync model to database '{$key}': " . $e->getMessage());
                }
            }
        }

        return $success;
    }

    /**
     * Sync this model to specific database
     */
    public function syncToDatabase(string $databaseKey): bool
    {
        try {
            $targetModel = static::onDatabase($databaseKey);
            
            // Check if record exists in target database
            $existing = $targetModel->where($this->getKeyName(), $this->getKey())->first();
            
            if ($existing) {
                // Update existing record
                $existing->fill($this->getAttributes());
                $existing->save();
            } else {
                // Create new record
                $newRecord = $targetModel->newInstance($this->getAttributes());
                $newRecord->save();
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to sync model to database '{$databaseKey}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database info for this model
     */
    public function getDatabaseInfo(): array
    {
        $databaseSwitchService = app(DatabaseSwitchService::class);
        $currentDatabase = $databaseSwitchService->getCurrentDatabase();
        $currentDatabaseInfo = $databaseSwitchService->getCurrentDatabaseInfo();
        
        return [
            'current_database' => $currentDatabase,
            'current_connection' => $this->getCurrentConnection(),
            'database_info' => $currentDatabaseInfo,
        ];
    }

    /**
     * Check if model exists in specific database
     */
    public function existsInDatabase(string $databaseKey): bool
    {
        try {
            $targetModel = static::onDatabase($databaseKey);
            return $targetModel->where($this->getKeyName(), $this->getKey())->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the same record from different database
     */
    public function fromDatabase(string $databaseKey): ?self
    {
        try {
            $targetModel = static::onDatabase($databaseKey);
            return $targetModel->where($this->getKeyName(), $this->getKey())->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Compare this model with the same record in another database
     */
    public function compareWithDatabase(string $databaseKey): array
    {
        $otherRecord = $this->fromDatabase($databaseKey);
        
        if (!$otherRecord) {
            return [
                'exists' => false,
                'differences' => [],
                'message' => 'Record does not exist in target database'
            ];
        }

        $differences = [];
        $attributes = $this->getAttributes();
        
        foreach ($attributes as $key => $value) {
            if ($otherRecord->getAttribute($key) !== $value) {
                $differences[$key] = [
                    'current' => $value,
                    'other' => $otherRecord->getAttribute($key)
                ];
            }
        }

        return [
            'exists' => true,
            'differences' => $differences,
            'is_identical' => empty($differences)
        ];
    }
}
