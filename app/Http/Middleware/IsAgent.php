<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow only authenticated Agent users
        if (Auth::check() && Auth::user()->isAgent()) {
            return $next($request);
        }

        // Redirect non-agent users or guests
        return redirect('/dashboard')->with('error', 'You do not have permission to access this area.');
    }
}