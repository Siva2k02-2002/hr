<?php

namespace App\Services;

use App\Models\AttendanceHolidayModel;
use App\Models\BranchModel;
use App\Models\CompanySettingModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\LeaveTypeModel;

/**
 * Read-through cache for the small, slow-changing lookup lists nearly every
 * form/filter/report on this app queries fresh on every page load —
 * departments, branches, designations, company settings, active holidays,
 * leave types. All are typically dozens of rows, re-read on almost every
 * request, and change maybe a few times a year.
 *
 * Every key is prefixed with the tenant's company code: this app's cache
 * store (CI4's file cache) is shared across every tenant hitting this PHP
 * install, not one per company — an unprefixed key would leak/collide
 * between different companies' data.
 *
 * Call invalidate() (or invalidateAll()) from whichever service actually
 * writes to the underlying table — see BranchService/DepartmentService/
 * DesignationService/SettingsController/AttendanceHolidayService/
 * LeaveTypesController for the call sites.
 */
class LookupCacheService
{
    private const TTL = 6 * HOUR;

    private function key(string $name): string
    {
        return 'lookup_' . tenant()->companyCode() . '_' . $name;
    }

    public function departments(): array
    {
        return cache()->remember($this->key('departments'), self::TTL, static fn () =>
            (new DepartmentModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll());
    }

    public function branches(): array
    {
        return cache()->remember($this->key('branches'), self::TTL, static fn () =>
            (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll());
    }

    public function designations(): array
    {
        return cache()->remember($this->key('designations'), self::TTL, static fn () =>
            (new DesignationModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll());
    }

    /**
     * Unlike the other lookups this row can also be changed from outside this app —
     * Super Admin writes it straight into the tenant database and cannot reach this
     * file cache. So the cached copy is only trusted while its updated_at still matches
     * the live row (one indexed single-row read); any mismatch re-reads and re-caches.
     */
    public function companySettings(): array
    {
        $key  = $this->key('company_settings');
        $live = service('tenantContext')->db()->table('company_settings')->select('updated_at')->orderBy('id', 'asc')->limit(1)->get()->getRowArray();

        $cached = cache()->get($key);
        if (is_array($cached) && ($cached['updated_at'] ?? null) === ($live['updated_at'] ?? null)) {
            return $cached;
        }

        $fresh = (new CompanySettingModel(service('tenantContext')->db()))->current() ?? [];
        cache()->save($key, $fresh, self::TTL);

        return $fresh;
    }

    public function leaveTypes(): array
    {
        return cache()->remember($this->key('leave_types'), self::TTL, static fn () =>
            (new LeaveTypeModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('sort_order')->findAll());
    }

    public function activeHolidays(): array
    {
        return cache()->remember($this->key('holidays'), self::TTL, static fn () =>
            (new AttendanceHolidayModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('date')->findAll());
    }

    public function invalidate(string $name): void
    {
        cache()->delete($this->key($name));
    }

    public function invalidateAll(): void
    {
        foreach (['departments', 'branches', 'designations', 'company_settings', 'leave_types', 'holidays'] as $name) {
            $this->invalidate($name);
        }
    }
}
