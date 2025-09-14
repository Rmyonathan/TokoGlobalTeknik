<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DatabaseSwitchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DatabaseSwitchController extends Controller
{
    protected $databaseSwitchService;

    public function __construct(DatabaseSwitchService $databaseSwitchService)
    {
        $this->databaseSwitchService = $databaseSwitchService;
    }

    /**
     * Show database switch interface
     */
    public function index()
    {
        $currentDatabase = $this->databaseSwitchService->getCurrentDatabaseInfo();
        $availableDatabases = $this->databaseSwitchService->getDatabaseStatus();
        $databaseSize = $this->databaseSwitchService->getDatabaseSize();
        
        return view('database-switch.index', compact(
            'currentDatabase', 
            'availableDatabases', 
            'databaseSize'
        ));
    }

    /**
     * Switch to specific database
     */
    public function switch(Request $request): JsonResponse
    {
        $request->validate([
            'database' => 'required|string|in:primary,secondary,testing,backup'
        ]);

        $databaseKey = $request->input('database');
        $previousDatabase = $this->databaseSwitchService->getCurrentDatabase();
        
        if ($this->databaseSwitchService->switchTo($databaseKey)) {
            // Log the switch
            $this->databaseSwitchService->logSwitch(
                $previousDatabase, 
                $databaseKey, 
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil diubah',
                'current_database' => $this->databaseSwitchService->getCurrentDatabaseInfo(),
                'database_size' => $this->databaseSwitchService->getDatabaseSize(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengubah database'
        ], 400);
    }

    /**
     * Get current database status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'current_database' => $this->databaseSwitchService->getCurrentDatabaseInfo(),
            'available_databases' => $this->databaseSwitchService->getDatabaseStatus(),
            'database_size' => $this->databaseSwitchService->getDatabaseSize(),
        ]);
    }

    /**
     * Test database connection
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'database' => 'required|string|in:primary,secondary,testing,backup'
        ]);

        $databaseKey = $request->input('database');
        $isConnected = $this->databaseSwitchService->testConnection($databaseKey);

        return response()->json([
            'success' => $isConnected,
            'message' => $isConnected ? 'Koneksi berhasil' : 'Koneksi gagal',
            'database' => $databaseKey,
        ]);
    }

    /**
     * Reset to default database
     */
    public function reset(): JsonResponse
    {
        if ($this->databaseSwitchService->resetToDefault()) {
            return response()->json([
                'success' => true,
                'message' => 'Database direset ke default',
                'current_database' => $this->databaseSwitchService->getCurrentDatabaseInfo(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mereset database'
        ], 400);
    }
}
