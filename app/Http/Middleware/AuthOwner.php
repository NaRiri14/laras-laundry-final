<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('login')) {
            return redirect('/');
        }
        if (session('level') != 'owner') {
            return redirect('/kasir');
        }
        return $next($request);
    }
}
