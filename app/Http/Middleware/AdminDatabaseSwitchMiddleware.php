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
        // Baca pilihan database dari session; fallback ke default dari config
        $selected = session('selected_database');
        $available = config('database.available_databases');

        if ($selected && isset($available[$selected])) {
            Config::set('database.connections.mariadb.database', $available[$selected]);
            app('db')->purge('mariadb');
            app('db')->reconnect('mariadb');
        }

        return $next($request);
    }
}