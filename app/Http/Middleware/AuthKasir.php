<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthKasir
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('login')) {
            return redirect('/');
        }
        return $next($request);
    }
}
