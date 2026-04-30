<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers to every response.
     * SEC-CRIT-06: Using replace=true to prevent duplicate headers from server config.
     * SEC-HIGH-01: Added HSTS for HTTPS enforcement.
     * SEC-HIGH-02: Added Content-Security-Policy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // SEC-CRIT-06: Replace any server-level duplicates
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // SEC-HIGH-01: HSTS — enforce HTTPS for 1 year
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // SEC-HIGH-02: Content Security Policy
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self'",
            "media-src 'self' blob:",
            "frame-ancestors 'self'",
        ]));

        return $response;
    }
}
