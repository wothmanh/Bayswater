<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictCalculatorProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has calculator role
        if (Auth::check() && Auth::user()->isCalculator()) {
            // Redirect calculator users away from profile pages
            return redirect()->route('calculator.create');
        }

        return $next($request);
    }
}