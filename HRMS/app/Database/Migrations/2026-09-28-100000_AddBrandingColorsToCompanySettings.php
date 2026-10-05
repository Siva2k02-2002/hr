<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-company color branding. Every column is nullable: NULL means "use the
 * platform default" (semantic colors) or "derive from the theme" (sidebar /
 * header), so an unconfigured tenant renders exactly as before.
 */
class AddBrandingColorsToCompanySettings extends Migration
{
    private const COLUMNS = [
        'primary_color', 'secondary_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
        'sidebar_bg_color', 'sidebar_active_color', 'sidebar_hover_color', 'header_bg_color', 'card_accent_color',
        'button_radius',
    ];

    public function up()
    {
        $fields = [];
        foreach (self::COLUMNS as $column) {
            $fields[$column] = $column === 'button_radius'
                ? ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'null' => true]
                : ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true];
        }
        $this->forge->addColumn('company_settings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('company_settings', self::COLUMNS);
    }
}
