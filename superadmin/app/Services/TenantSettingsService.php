<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Reads and updates ONE company's tenant `company_settings` row (never the
 * platform `companies` table). The tenant connection always comes from
 * TenantConnectionFactory::forCompany(), which resolves it from that company's
 * own stored credentials and verifies the connected database name.
 *
 * Only the fields in EDITABLE are ever written. System-controlled columns
 * (id, created_at, updated_at, permissions_version, employee_code_next_seq,
 * employee_code_prefix and the *_path upload columns) are read-only here.
 *
 * Validation mirrors what HRMS itself accepts: SettingsController's option
 * lists, branding_normalize_hex() (#RRGGBB) and company_branding_colors()
 * (button_radius clamped to 0-24).
 */
class TenantSettingsService
{
    public const DATE_FORMATS = ['d-m-Y', 'm-d-Y', 'Y-m-d'];
    public const TIME_FORMATS = ['24h' => '24-hour', '12h' => '12-hour'];
    public const THEMES       = ['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Match device'];
    public const FONTS        = ['system' => 'System default', 'inter' => 'Inter', 'roboto' => 'Roboto'];
    public const LANGUAGES    = ['en' => 'English', 'hi' => 'Hindi'];
    public const WEEK_STARTS  = [1 => 'Monday', 0 => 'Sunday'];

    /** Optional colors: blank saves NULL, which HRMS reads as "use the default / derive from theme". */
    public const COLOR_FIELDS = [
        'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
        'sidebar_bg_color', 'sidebar_active_color', 'sidebar_hover_color', 'header_bg_color', 'card_accent_color',
    ];

    public const EDITABLE = [
        'company_name', 'theme', 'font_family',
        'default_language', 'week_start_day', 'timezone', 'currency', 'date_format', 'time_format',
        'require_email_verification',
        'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
        'sidebar_bg_color', 'sidebar_active_color', 'sidebar_hover_color', 'header_bg_color', 'card_accent_color',
        'button_radius',
    ];

    public const READ_ONLY = [
        'id', 'created_at', 'updated_at', 'permissions_version',
        'employee_code_prefix', 'employee_code_next_seq',
        'logo_path', 'favicon_path', 'banner_path',
    ];

    public function __construct(
        private TenantConnectionFactory $factory = new TenantConnectionFactory(),
        private AuditService $audit = new AuditService()
    ) {
    }

    /**
     * @return array{settings: array, missing: string[]} missing = editable columns the tenant schema lacks
     *
     * @throws RuntimeException
     */
    public function load(int $companyId): array
    {
        $db  = $this->factory->forCompany($companyId);
        $row = $this->row($db);

        return [
            'settings' => $row,
            'missing'  => array_values(array_diff(self::EDITABLE, array_keys($row))),
        ];
    }

    /**
     * Validates, then updates only the changed whitelisted fields of the tenant's
     * single settings row. Returns [] when nothing changed.
     *
     * @return array{errors: array<string,string>, changed: string[]}
     *
     * @throws RuntimeException on connection / schema / write failure
     */
    public function update(int $companyId, array $input): array
    {
        $db  = $this->factory->forCompany($companyId);
        $old = $this->row($db);

        [$clean, $errors] = $this->validate($input, array_keys($old));
        if ($errors !== []) {
            return ['errors' => $errors, 'changed' => []];
        }

        $before = [];
        $after  = [];
        foreach ($clean as $field => $value) {
            if ($this->normalize($old[$field] ?? null) !== $this->normalize($value)) {
                $before[$field] = $old[$field] ?? null;
                $after[$field]  = $value;
            }
        }

        if ($after === []) {
            return ['errors' => [], 'changed' => []];
        }

        $after['updated_at'] = date('Y-m-d H:i:s');

        // Exactly the one row HRMS reads (lowest id), by primary key — never a bare UPDATE.
        $db->table('company_settings')->where('id', (int) $old['id'])->update($after);
        if ($db->error()['code'] ?? 0) {
            throw new RuntimeException('The company database rejected the update.');
        }
        unset($after['updated_at']);

        $this->audit->log('update', 'company_settings', 'company_setting', (int) $old['id'], $before, $after, $companyId);

        return ['errors' => [], 'changed' => array_keys($after)];
    }

    /** @throws RuntimeException */
    private function row(BaseConnection $db): array
    {
        try {
            $row = $db->table('company_settings')->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
        } catch (\Throwable) {
            throw new RuntimeException('Could not read company_settings from the company\'s database.');
        }

        if (! $row) {
            throw new RuntimeException('The company\'s database has no company_settings row.');
        }

        return $row;
    }

    /**
     * @param string[] $availableColumns columns that exist in the tenant schema
     *
     * @return array{0: array<string,mixed>, 1: array<string,string>}
     */
    private function validate(array $in, array $availableColumns): array
    {
        $clean  = [];
        $errors = [];
        $has    = static fn (string $f): bool => in_array($f, $availableColumns, true);
        $str    = static fn (string $f): string => trim((string) ($in[$f] ?? ''));

        if ($has('company_name')) {
            $v = $str('company_name');
            if (mb_strlen($v) < 2 || mb_strlen($v) > 150) {
                $errors['company_name'] = 'Company name must be 2-150 characters.';
            }
            $clean['company_name'] = $v;
        }

        if ($has('timezone')) {
            $v = $str('timezone');
            if (! in_array($v, \DateTimeZone::listIdentifiers(), true)) {
                $errors['timezone'] = 'Choose a valid timezone.';
            }
            $clean['timezone'] = $v;
        }

        if ($has('currency')) {
            $v = strtoupper($str('currency'));
            if (preg_match('/^[A-Z]{3}$/', $v) !== 1) {
                $errors['currency'] = 'Currency must be a 3-letter code such as INR.';
            }
            $clean['currency'] = $v;
        }

        $enums = [
            'date_format'      => array_combine(self::DATE_FORMATS, self::DATE_FORMATS),
            'time_format'      => self::TIME_FORMATS,
            'theme'            => self::THEMES,
            'font_family'      => self::FONTS,
            'default_language' => self::LANGUAGES,
        ];
        foreach ($enums as $field => $allowed) {
            if (! $has($field)) {
                continue;
            }
            $v = $str($field);
            if (! array_key_exists($v, $allowed)) {
                $errors[$field] = 'Choose one of the listed options.';
            }
            $clean[$field] = $v;
        }

        if ($has('week_start_day')) {
            $v = $str('week_start_day');
            if (! in_array($v, ['0', '1'], true)) {
                $errors['week_start_day'] = 'Choose Monday or Sunday.';
            }
            $clean['week_start_day'] = (int) $v;
        }

        if ($has('require_email_verification')) {
            $clean['require_email_verification'] = ! empty($in['require_email_verification']) ? 1 : 0;
        }

        foreach (self::COLOR_FIELDS as $field) {
            if (! $has($field)) {
                continue;
            }
            $raw = $str($field);
            if ($raw === '') {
                $clean[$field] = null;

                continue;
            }
            $hex = self::hex($raw);
            if ($hex === null) {
                $errors[$field] = 'Enter a valid color such as #5B3DF5, or leave blank for the default.';
            }
            $clean[$field] = $hex;
        }

        if ($has('button_radius')) {
            $raw = $str('button_radius');
            if ($raw === '') {
                $clean['button_radius'] = null;
            } elseif (preg_match('/^\d{1,2}$/', $raw) === 1 && (int) $raw <= 24) {
                $clean['button_radius'] = (int) $raw;
            } else {
                $errors['button_radius'] = 'Button radius must be a whole number from 0 to 24, or blank.';
                $clean['button_radius'] = null;
            }
        }

        return [$clean, $errors];
    }

    /** Same rule as HRMS branding_normalize_hex(): #RGB or #RRGGBB in, uppercase #RRGGBB out. */
    public static function hex(string $hex): ?string
    {
        $hex = trim($hex);
        if (preg_match('/^#([0-9a-fA-F]{3})$/', $hex, $m)) {
            $hex = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1 ? strtoupper($hex) : null;
    }

    private function normalize(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        // Case-insensitive compare for colors stored by older code in lowercase.
        return is_string($v) && preg_match('/^#[0-9a-f]{6}$/i', $v) === 1 ? strtoupper($v) : (string) $v;
    }
}
