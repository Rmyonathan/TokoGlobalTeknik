<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Allow access to login page without authentication
        if (!Auth::check() && !$request->is('signin')) {
            return redirect()->route('signin');
        }

        return $next($request);
    }
}

