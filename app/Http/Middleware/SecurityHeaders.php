<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only set HSTS on HTTPS and non-local environments
        if ($request->isSecure() && in_array(env('APP_ENV'), ['production', 'staging'])) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Core security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=(), interest-cohort=()");

        // Conservative CSP suitable for existing inline scripts (can be tightened later)
        $csp = [];
        $csp[] = "default-src 'self'";
        $csp[] = "base-uri 'self'";
        $csp[] = "frame-ancestors 'none'";
        $csp[] = "img-src 'self' data: https:";
        $csp[] = "font-src 'self' data:";
        $csp[] = "style-src 'self' 'unsafe-inline'";
        $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
        $csp[] = "connect-src 'self'";
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}