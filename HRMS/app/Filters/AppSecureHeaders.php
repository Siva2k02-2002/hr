<?php

namespace App\Filters;

use CodeIgniter\Filters\SecureHeaders;

/**
 * Stock SecureHeaders sends "Referrer-Policy: same-origin", which strips the
 * Referer on cross-origin requests. OpenStreetMap's tile servers reject
 * requests without a Referer ("403 Access blocked"), so the Leaflet maps
 * rendered grey. strict-origin-when-cross-origin sends only the origin
 * (no path/query) to other sites, and nothing on https -> http downgrades.
 */
class AppSecureHeaders extends SecureHeaders
{
    protected $headers = [
        'X-Frame-Options'                   => 'SAMEORIGIN',
        'X-Content-Type-Options'            => 'nosniff',
        'X-Download-Options'                => 'noopen',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        'Referrer-Policy'                   => 'strict-origin-when-cross-origin',
    ];
}
