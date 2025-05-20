<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class AdminDatabaseSwitchMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always use the single database regardless of role
        Config::set('database.connections.mariadb.database', 'atapjeri');
        
        app('db')->purge('mariadb');
        app('db')->reconnect('mariadb');

        return $next($request);
    }
}