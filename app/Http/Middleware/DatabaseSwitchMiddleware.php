<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DatabaseSwitchService;
use Illuminate\Support\Facades\Config;

class DatabaseSwitchMiddleware
{
    protected $databaseSwitchService;

    public function __construct(DatabaseSwitchService $databaseSwitchService)
    {
        $this->databaseSwitchService = $databaseSwitchService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get current database from session
        $currentDatabase = $this->databaseSwitchService->getCurrentDatabase();
        
        // Switch to the selected database
        if ($this->databaseSwitchService->switchTo($currentDatabase)) {
            // Set the default connection for this request
            $connection = $this->databaseSwitchService->getCurrentConnection();
            Config::set('database.default', $connection);
        }

        // Add database info to request for use in views
        $request->merge([
            'current_database' => $this->databaseSwitchService->getCurrentDatabaseInfo(),
            'available_databases' => $this->databaseSwitchService->getDatabaseStatus(),
        ]);

        return $next($request);
    }
}
