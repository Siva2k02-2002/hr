<?php

use App\Services\TenantSettingsService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Validation rules for the per-company tenant settings form. Pure logic — no
 * database. Mirrors what HRMS itself accepts (see TenantSettingsService docblock).
 *
 * @internal
 */
final class TenantSettingsServiceTest extends CIUnitTestCase
{
    /** Every editable column present, as in a fully migrated tenant database. */
    private function validate(array $input): array
    {
        $m = new ReflectionMethod(TenantSettingsService::class, 'validate');
        $m->setAccessible(true);

        return $m->invoke(new TenantSettingsService(), $input, TenantSettingsService::EDITABLE);
    }

    private function valid(array $override = []): array
    {
        return $override + [
            'company_name' => 'Acme Ltd', 'theme' => 'dark', 'accent_color' => '#1f6f5c', 'font_family' => 'inter',
            'default_language' => 'en', 'week_start_day' => '1', 'timezone' => 'Asia/Kolkata', 'currency' => 'inr',
            'date_format' => 'd-m-Y', 'time_format' => '24h', 'primary_color' => '#5b3df5', 'button_radius' => '8',
        ];
    }

    public function testValidInputIsNormalized(): void
    {
        [$clean, $errors] = $this->validate($this->valid());

        $this->assertSame([], $errors);
        $this->assertSame('INR', $clean['currency']);
        $this->assertSame('#1F6F5C', $clean['accent_color']);
        $this->assertSame('#5B3DF5', $clean['primary_color']);
        $this->assertSame(8, $clean['button_radius']);
        $this->assertSame(0, $clean['require_email_verification']);
        $this->assertNull($clean['sidebar_bg_color'], 'blank optional color saves NULL');
    }

    public function testShortHexIsExpanded(): void
    {
        $this->assertSame('#AABBCC', TenantSettingsService::hex('#abc'));
        $this->assertNull(TenantSettingsService::hex('red'));
        $this->assertNull(TenantSettingsService::hex('#12345'));
        $this->assertNull(TenantSettingsService::hex('#GGGGGG'));
        $this->assertNull(TenantSettingsService::hex('url(x)'));
    }

    /** @dataProvider badValues */
    public function testInvalidValuesAreRejected(string $field, string $value): void
    {
        [, $errors] = $this->validate($this->valid([$field => $value]));

        $this->assertArrayHasKey($field, $errors);
    }

    public static function badValues(): array
    {
        return [
            'name too short'    => ['company_name', 'A'],
            'name too long'     => ['company_name', str_repeat('x', 151)],
            'bad timezone'      => ['timezone', 'Mars/Phobos'],
            'bad currency'      => ['currency', 'RUPEES'],
            'currency digits'   => ['currency', 'I2R'],
            'bad date format'   => ['date_format', 'Y/m/d'],
            'bad time format'   => ['time_format', '36h'],
            'bad theme'         => ['theme', 'neon'],
            'bad font'          => ['font_family', 'comic'],
            'bad language'      => ['default_language', 'fr'],
            'bad week start'    => ['week_start_day', '2'],
            'accent required'   => ['accent_color', ''],
            'accent not hex'    => ['accent_color', 'blue'],
            'css injection'     => ['primary_color', '#fff;}body{display:none'],
            'sidebar not hex'   => ['sidebar_bg_color', '12345'],
            'radius too big'    => ['button_radius', '25'],
            'radius negative'   => ['button_radius', '-1'],
            'radius not number' => ['button_radius', '8px'],
        ];
    }

    public function testBlankRadiusAndColorsSaveNull(): void
    {
        [$clean, $errors] = $this->validate($this->valid(['button_radius' => '', 'primary_color' => '']));

        $this->assertSame([], $errors);
        $this->assertNull($clean['button_radius']);
        $this->assertNull($clean['primary_color']);
    }

    public function testSystemFieldsCanNeverBeWritten(): void
    {
        [$clean] = $this->validate($this->valid([
            'id' => '9', 'permissions_version' => '99', 'employee_code_next_seq' => '1', 'employee_code_prefix' => 'X',
            'logo_path' => '../../x', 'created_at' => '2000-01-01', 'updated_at' => '2000-01-01',
        ]));

        foreach (TenantSettingsService::READ_ONLY as $field) {
            $this->assertArrayNotHasKey($field, $clean);
            $this->assertNotContains($field, TenantSettingsService::EDITABLE);
        }
    }

    public function testColumnsMissingFromTenantSchemaAreSkipped(): void
    {
        $m = new ReflectionMethod(TenantSettingsService::class, 'validate');
        $m->setAccessible(true);
        [$clean, $errors] = $m->invoke(new TenantSettingsService(), $this->valid(), ['id', 'company_name', 'timezone']);

        $this->assertSame([], $errors);
        $this->assertSame(['company_name', 'timezone'], array_keys($clean));
    }
}
