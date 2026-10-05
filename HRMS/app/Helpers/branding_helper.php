<?php

/**
 * Company color branding.
 *
 * The stylesheets never contain brand colors: they read CSS variables, and
 * company_branding_style() emits those variables per tenant (light + dark).
 * Everything derived from a picked color — hover, soft background, readable
 * text on light/dark surfaces, text-on-color — is computed here so a company
 * only ever chooses the base colors and every state stays legible.
 *
 * The defaults below are duplicated as static fallbacks in
 * assets/css/theme.css (for pages rendered without the injected
 * <style>, e.g. bare error pages) and in the superadmin Branding form's JS
 * (live preview + "Restore default"). Keep the three in sync.
 */

if (! defined('BRANDING_LIGHT_SURFACE')) {
    define('BRANDING_LIGHT_SURFACE', '#FFFFFF');
    define('BRANDING_DARK_SURFACE', '#171922');
    define('BRANDING_INK', '#111827');
}

if (! function_exists('branding_defaults')) {
    /** Semantic colors; sidebar/header/card accent are "auto" (null) by default. */
    function branding_defaults(): array
    {
        return [
            'primary_color'   => '#5B3DF5',
            'secondary_color' => '#64748B',
            'success_color'   => '#16A34A',
            'warning_color'   => '#EA580C',
            'danger_color'    => '#DC2626',
            'info_color'      => '#2563EB',
        ];
    }
}

if (! function_exists('branding_color_fields')) {
    /** Every color column on company_settings that the branding form edits. */
    function branding_color_fields(): array
    {
        return [
            'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
            'sidebar_bg_color', 'sidebar_active_color', 'sidebar_hover_color', 'header_bg_color', 'card_accent_color',
        ];
    }
}

if (! function_exists('branding_normalize_hex')) {
    function branding_normalize_hex(?string $hex): ?string
    {
        $hex = trim((string) $hex);
        if (preg_match('/^#([0-9a-fA-F]{3})$/', $hex, $m)) {
            $hex = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? strtoupper($hex) : null;
    }
}

if (! function_exists('branding_rgb')) {
    /** @return array{0:int,1:int,2:int} */
    function branding_rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }
}

if (! function_exists('branding_rgb_string')) {
    function branding_rgb_string(string $hex): string
    {
        return implode(', ', branding_rgb($hex));
    }
}

if (! function_exists('branding_hex')) {
    function branding_hex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', max(0, min(255, (int) round($rgb[0]))), max(0, min(255, (int) round($rgb[1]))), max(0, min(255, (int) round($rgb[2]))));
    }
}

if (! function_exists('branding_hsl')) {
    /** @return array{0:float,1:float,2:float} h in degrees, s/l in 0..1 */
    function branding_hsl(string $hex): array
    {
        [$r, $g, $b] = array_map(static fn ($v) => $v / 255, branding_rgb($hex));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l   = ($max + $min) / 2;
        $d   = $max - $min;

        if ($d == 0) {
            return [0.0, 0.0, $l];
        }

        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r      => (($g - $b) / $d) + ($g < $b ? 6 : 0),
            $g      => (($b - $r) / $d) + 2,
            default => (($r - $g) / $d) + 4,
        };

        return [$h * 60, $s, $l];
    }
}

if (! function_exists('branding_from_hsl')) {
    function branding_from_hsl(float $h, float $s, float $l): string
    {
        $l = max(0.0, min(1.0, $l));
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60  => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default  => [$c, 0, $x],
        };

        return branding_hex([($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255]);
    }
}

if (! function_exists('branding_mix')) {
    /** $weight is the share of $a (0..1); the rest is $b. */
    function branding_mix(string $a, string $b, float $weight): string
    {
        $ca = branding_rgb($a);
        $cb = branding_rgb($b);

        return branding_hex([
            $ca[0] * $weight + $cb[0] * (1 - $weight),
            $ca[1] * $weight + $cb[1] * (1 - $weight),
            $ca[2] * $weight + $cb[2] * (1 - $weight),
        ]);
    }
}

if (! function_exists('branding_darken')) {
    function branding_darken(string $hex, float $amount): string
    {
        [$h, $s, $l] = branding_hsl($hex);

        return branding_from_hsl($h, $s, $l - $amount);
    }
}

if (! function_exists('branding_lighten')) {
    function branding_lighten(string $hex, float $amount): string
    {
        [$h, $s, $l] = branding_hsl($hex);

        return branding_from_hsl($h, $s, $l + $amount);
    }
}

if (! function_exists('branding_luminance')) {
    function branding_luminance(string $hex): float
    {
        $lin = array_map(static function ($v) {
            $v /= 255;

            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, branding_rgb($hex));

        return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
    }
}

if (! function_exists('branding_contrast')) {
    function branding_contrast(string $a, string $b): float
    {
        $la = branding_luminance($a);
        $lb = branding_luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }
}

if (! function_exists('branding_on_color')) {
    /** Black-ish or white text — whichever reads better on $hex. */
    function branding_on_color(string $hex): string
    {
        return branding_contrast($hex, '#FFFFFF') >= branding_contrast($hex, BRANDING_INK) ? '#FFFFFF' : BRANDING_INK;
    }
}

if (! function_exists('branding_readable')) {
    /**
     * $hex nudged in lightness until it reaches $min contrast on $background
     * (lighter on dark backgrounds, darker on light ones). Hue and saturation
     * are kept, so it still reads as the brand color.
     */
    function branding_readable(string $hex, string $background, float $min = 4.5): string
    {
        $dark = branding_luminance($background) < 0.4;
        $out  = $hex;

        for ($i = 0; $i < 60 && branding_contrast($out, $background) < $min; $i++) {
            $out = $dark ? branding_lighten($out, 0.02) : branding_darken($out, 0.02);
        }

        return $out;
    }
}

if (! function_exists('company_branding_colors')) {
    /** Saved colors merged over the defaults; unset/invalid optional colors stay null. */
    function company_branding_colors(?array $settings = null): array
    {
        $settings ??= company_branding_settings();
        $colors    = [];

        foreach (branding_color_fields() as $field) {
            $colors[$field] = branding_normalize_hex($settings[$field] ?? null) ?? (branding_defaults()[$field] ?? null);
        }

        $radius            = $settings['button_radius'] ?? null;
        $colors['button_radius'] = ($radius !== null && $radius !== '') ? max(0, min(24, (int) $radius)) : null;

        return $colors;
    }
}

if (! function_exists('branding_theme_tokens')) {
    /**
     * Per-theme derived tokens for the six semantic colors.
     *
     * @param  'light'|'dark' $mode
     * @return array<string,string>
     */
    function branding_theme_tokens(array $colors, string $mode): array
    {
        $surface = $mode === 'dark' ? BRANDING_DARK_SURFACE : BRANDING_LIGHT_SURFACE;
        $tokens  = [];

        foreach (['primary', 'secondary', 'success', 'warning', 'danger', 'info'] as $name) {
            $base  = $colors[$name . '_color'];
            $hover = $mode === 'dark' ? branding_lighten($base, 0.06) : branding_darken($base, 0.07);
            $soft  = branding_mix($base, $surface, $mode === 'dark' ? 0.20 : 0.12);
            $text  = branding_readable($base, $soft);
            $sub   = branding_mix($base, $surface, $mode === 'dark' ? 0.40 : 0.35);

            $tokens["--color-{$name}-hover"]  = $hover;
            $tokens["--color-{$name}-soft"]   = $soft;
            $tokens["--color-{$name}-text"]   = $text;
            $tokens["--text-on-{$name}-hover"] = branding_on_color($hover);

            $tokens["--bs-{$name}-text-emphasis"] = $text;
            $tokens["--bs-{$name}-bg-subtle"]     = $soft;
            $tokens["--bs-{$name}-border-subtle"] = $sub;
        }

        // Brand accent used for links, focus rings and "active" chrome.
        $tokens['--bs-link-color']       = $tokens['--color-primary-text'];
        $tokens['--bs-link-hover-color'] = $tokens['--color-primary-hover'];
        $tokens['--shadow-focus']        = '0 0 0 3px rgba(' . branding_rgb_string($colors['primary_color']) . ', .28)';

        return $tokens;
    }
}

if (! function_exists('branding_declarations')) {
    function branding_declarations(array $tokens): string
    {
        $out = '';
        foreach ($tokens as $name => $value) {
            $out .= $name . ':' . $value . ';';
        }

        return $out;
    }
}

if (! function_exists('branding_sidebar_tokens')) {
    /** Only emitted for the parts a company actually customised. */
    function branding_sidebar_tokens(array $colors): array
    {
        $tokens = [];

        if ($colors['sidebar_bg_color']) {
            $bg   = $colors['sidebar_bg_color'];
            $ink  = branding_on_color($bg);
            $tokens += [
                '--color-surface'        => $bg,
                '--color-surface-2'      => branding_mix($ink, $bg, 0.08),
                '--color-border'         => branding_mix($ink, $bg, 0.14),
                '--color-text'           => $ink,
                '--color-text-secondary' => branding_mix($ink, $bg, 0.78),
                '--color-muted'          => branding_mix($ink, $bg, 0.55),
                // The page-level brand tokens were tuned for the page surface, not this one.
                '--color-primary-text'   => branding_readable($colors['primary_color'], $bg, 4.5),
            ];

            if (! $colors['sidebar_active_color']) {
                $activeBg = branding_mix($colors['primary_color'], $bg, 0.28);
                $tokens['--sidebar-active-bg']   = $activeBg;
                $tokens['--sidebar-active-text'] = branding_readable($colors['primary_color'], $activeBg, 4.5);
                $tokens['--sidebar-active-bar']  = branding_readable($colors['primary_color'], $bg, 3.0);
            }
        }

        if ($colors['sidebar_hover_color']) {
            $tokens['--sidebar-hover-bg']   = $colors['sidebar_hover_color'];
            $tokens['--sidebar-hover-text'] = branding_on_color($colors['sidebar_hover_color']);
        }

        if ($colors['sidebar_active_color']) {
            $tokens['--sidebar-active-bg']   = $colors['sidebar_active_color'];
            $tokens['--sidebar-active-text'] = branding_on_color($colors['sidebar_active_color']);
            $tokens['--sidebar-active-bar']  = branding_on_color($colors['sidebar_active_color']);
        }

        return $tokens;
    }
}

if (! function_exists('company_branding_css')) {
    /** The complete override stylesheet body for the current tenant (no <style> tags). */
    function company_branding_css(?array $settings = null): string
    {
        $c = company_branding_colors($settings);

        $shared = [];
        foreach (['primary', 'secondary', 'success', 'warning', 'danger', 'info'] as $name) {
            $base = $c[$name . '_color'];
            $shared["--color-{$name}"]     = $base;
            $shared["--color-{$name}-rgb"] = branding_rgb_string($base);
            $shared["--text-on-{$name}"]   = branding_on_color($base);
            $shared["--bs-{$name}"]        = $base;
            $shared["--bs-{$name}-rgb"]    = branding_rgb_string($base);
        }
        $shared['--text-on-sidebar'] = $c['sidebar_bg_color'] ? branding_on_color($c['sidebar_bg_color']) : 'var(--color-text)';

        if ($c['card_accent_color']) {
            $shared['--card-accent'] = $c['card_accent_color'];
        }
        if ($c['button_radius'] !== null) {
            $shared['--btn-radius'] = $c['button_radius'] . 'px';
        }
        if ($c['header_bg_color']) {
            $ink = branding_on_color($c['header_bg_color']);
            $shared += [
                '--header-bg'     => $c['header_bg_color'],
                '--header-text'   => $ink,
                '--header-muted'  => branding_mix($ink, $c['header_bg_color'], 0.72),
                '--header-border' => branding_mix($ink, $c['header_bg_color'], 0.14),
                '--header-hover'  => branding_mix($ink, $c['header_bg_color'], 0.10),
            ];
        }

        $css  = ':root{' . branding_declarations($shared + branding_theme_tokens($c, 'light')) . '}';
        $css .= ':root[data-theme="dark"]{' . branding_declarations(branding_theme_tokens($c, 'dark')) . '}';

        $sidebar = branding_sidebar_tokens($c);
        if ($sidebar) {
            $css .= '.sidebar{' . branding_declarations($sidebar) . '}';
        }

        return $css;
    }
}

if (! function_exists('company_branding_style')) {
    function company_branding_style(): string
    {
        return '<style id="company-branding">' . company_branding_css() . '</style>';
    }
}
