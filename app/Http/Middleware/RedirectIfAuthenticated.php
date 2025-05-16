<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Check if the authenticated user's role is "Administrator"
                if (Auth::user()->role === 'Administrator') {
                    return $next($request);  // Allow "Administrator" to proceed without redirection
                }

                // Skip redirection if the request is accessing the registration page
                if ($request->routeIs('register')) {
                    return $next($request);  // Continue to the registration page
                }

                // Redirect other authenticated users to the dashboard
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}























// namespace App\Http\Middleware;

// use App\Providers\RouteServiceProvider;
// use Closure;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class RedirectIfAuthenticated
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
//      * @param  string|null  ...$guards
//      * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
//      */
//     public function handle(Request $request, Closure $next, ...$guards)
//     {
//         $guards = empty($guards) ? [null] : $guards;

//         foreach ($guards as $guard) {
//             if (Auth::guard($guard)->check()) {
//                 return redirect(RouteServiceProvider::HOME);
//             }
//         }

//         return $next($request);
//     }
// }

