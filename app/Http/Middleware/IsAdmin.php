<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has the 'admin' role
        if (Auth::check() && Auth::user()->isAdmin()) {
             return $next($request);
        }

        // Redirect non-admin users or guests
        // You might want to redirect to a specific route or show a 403 Forbidden error
        // abort(403, 'Unauthorized action.');
        return redirect('/dashboard')->with('error', 'You do not have permission to access this area.');
    }
}
