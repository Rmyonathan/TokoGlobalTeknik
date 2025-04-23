<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is logged in
        // if (!Auth::check()) {
        //     return redirect()->route('signin')->with('error', 'Please log in.');
        // }

        // Check if the user is an admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Access denied. Admins only.');
        }

        return $next($request);
    }
}
