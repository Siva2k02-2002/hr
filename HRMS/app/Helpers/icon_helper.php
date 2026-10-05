<?php

if (! function_exists('icon')) {
    /**
     * Renders a Lucide icon placeholder. Lucide has no PHP renderer, so this
     * emits the standard `data-lucide` marker its browser script replaces
     * with inline SVG on DOMContentLoaded (see app.js) — the zero-build
     * integration path, since this app has no bundler.
     *
     * $name is a Lucide icon name (kebab-case, e.g. 'pencil', 'square-pen'),
     * not a Bootstrap Icons name — views were migrated by hand from the old
     * `bi-*` classes.
     */
    function icon(string $name, string $class = '', array $attrs = []): string
    {
        $classAttr = trim('icon ' . $class);
        $extra = '';
        foreach ($attrs as $key => $value) {
            $extra .= ' ' . esc($key, 'attr') . '="' . esc((string) $value, 'attr') . '"';
        }

        return '<i data-lucide="' . esc($name, 'attr') . '" class="' . esc($classAttr, 'attr') . '" aria-hidden="true"' . $extra . '></i>';
    }
}
