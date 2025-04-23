<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;

class AdminDatabaseSwitchMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role;

            $databases = config('database.available_databases');

            // Jika admin, cek database yang dipilih dari sesi
            if ($role === 'admin') {
                if (!session()->has('selected_database')) {
                    $selectedDatabase = session('selected_database', 'first');
                }

                $selectedDatabase = session('selected_database'); // Retrieve session value

                if (is_array($databases) && array_key_exists($selectedDatabase, $databases)) {
                    Config::set('database.connections.mariadb.database', $databases[$selectedDatabase]);

                    // Artisan::call('config:clear');
                    // Artisan::call('cache:clear');
                    // Artisan::call('config:cache'); // Re-cache the configuration

                    app('db')->purge('mariadb');
                    app('db')->reconnect('mariadb');
                }
            }
            // Jika bukan admin, gunakan database sesuai role
            elseif (array_key_exists($role, $databases)) {
                Config::set('database.connections.mariadb.database', $databases[$role]);

                app('db')->purge('mariadb');
                app('db')->reconnect('mariadb');
            }
            // Jika tidak ada mapping, fallback ke default
            else {
                Config::set('database.connections.mariadb.database', 'first_database');

                app('db')->purge('mariadb');
                app('db')->reconnect('mariadb');
            }
        }

        return $next($request);
    }
}
