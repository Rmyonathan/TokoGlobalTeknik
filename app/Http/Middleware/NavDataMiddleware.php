<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Saldo;


class NavDataMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $saldo = Saldo::find(1);

        View::share('saldo', $saldo);

        return $next($request);
    }
}

