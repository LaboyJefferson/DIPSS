<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckDefaultPassword
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated and using a default password
        if (Auth::check() && Auth::user()->default_password) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}

