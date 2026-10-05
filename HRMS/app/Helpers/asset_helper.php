<?php

if (! function_exists('asset_url')) {
    /**
     * base_url() for a local /assets/... file, with a cache-busting
     * ?v= query string derived from the file's own mtime — a browser (or
     * intermediate proxy/CDN) that cached the old CSS/JS from before a
     * deploy picks up the new version automatically instead of needing a
     * hard refresh, without needing a build step to rename files.
     */
    function asset_url(string $relativePath): string
    {
        $absolutePath = FCPATH . ltrim($relativePath, '/');
        $version      = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';

        return base_url($relativePath) . '?v=' . $version;
    }
}
