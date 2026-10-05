<?php

namespace App\Models;

use CodeIgniter\Model;

/** Single-row table — always operate on the first (and only) row. */
class CompanySettingModel extends Model
{
    protected $table         = 'company_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'company_name', 'timezone', 'currency', 'date_format', 'time_format', 'permissions_version', 'require_email_verification',
        'logo_path', 'favicon_path', 'banner_path', 'theme', 'accent_color', 'font_family', 'default_language', 'week_start_day',
        'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
        'sidebar_bg_color', 'sidebar_active_color', 'sidebar_hover_color', 'header_bg_color', 'card_accent_color', 'button_radius',
    ];

    public function current(): ?array
    {
        return $this->orderBy('id', 'asc')->first();
    }

    /** Bump whenever a role's permissions or a user's role assignment changes. */
    public function bumpPermissionsVersion(): void
    {
        $row = $this->current();
        if ($row) {
            $this->update($row['id'], ['permissions_version' => (int) $row['permissions_version'] + 1]);
        }
    }
}
