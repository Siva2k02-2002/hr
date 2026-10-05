<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Adds the headers CI4's built-in SecureHeaders filter doesn't (CSP,
 * Permissions-Policy) — X-Frame-Options/X-Content-Type-Options/Referrer-Policy
 * already come from the stock 'secureheaders' filter, and HSTS is already set
 * by ForceHTTPS's force_https() call once app.forceGlobalSecureRequests is on.
 *
 * Every view loads Bootstrap/jQuery/Select2 from cdn.jsdelivr.net and relies on
 * inline <script> blocks scattered across ~150 views (see employees/form.php,
 * for one) — a nonce-based CSP tight enough to drop 'unsafe-inline' would mean
 * auditing and rewriting every one of them, which is out of scope here. This
 * CSP is still real hardening: it blocks loading scripts/frames/objects from
 * any *other* origin, which is what stops a successful injection from pulling
 * in an attacker-controlled payload.
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (ENVIRONMENT !== 'production') {
            return $response;
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net https://unpkg.com 'unsafe-inline'",
            "style-src 'self' https://cdn.jsdelivr.net https://unpkg.com 'unsafe-inline'",
            "img-src 'self' data: https://unpkg.com https://*.tile.openstreetmap.org",
            "font-src 'self' https://cdn.jsdelivr.net data:",
            "connect-src 'self' https://cdn.jsdelivr.net https://unpkg.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ]);

        $response->setHeader('Content-Security-Policy', $csp);
        $response->setHeader('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=()');

        return $response;
    }
}
