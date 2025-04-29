<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if ($role == 'admin') {
            return $next($request);
        }

        if (!Auth::check() || !Auth::user()->hasRole($role)) {
            return redirect()->back()->with('error', "You Don't Access this URL");

        }
        return $next($request);
    }
}
