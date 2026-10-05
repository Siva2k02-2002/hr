<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Company branding/appearance fields (Phase 12). financial_year_start_month
 * already lives on leave_settings and working-day patterns are already
 * handled per-branch by attendance_weekly_offs — neither is duplicated here.
 */
class AddBrandingToCompanySettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('company_settings', [
            'logo_path'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'company_name'],
            'favicon_path'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'logo_path'],
            'banner_path'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'favicon_path'],
            'theme'            => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'light', 'after' => 'banner_path'],
            'accent_color'     => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#1f6f5c', 'after' => 'theme'],
            'font_family'      => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'system', 'after' => 'accent_color'],
            'default_language' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'en', 'after' => 'font_family'],
            'week_start_day'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'default_language'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('company_settings', [
            'logo_path', 'favicon_path', 'banner_path', 'theme', 'accent_color', 'font_family', 'default_language', 'week_start_day',
        ]);
    }
}
