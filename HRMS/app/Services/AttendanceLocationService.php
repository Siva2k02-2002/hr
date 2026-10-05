<?php

namespace App\Services;

use App\Models\AttendanceLocationModel;
use RuntimeException;

class AttendanceLocationService
{
    private AttendanceLocationModel $locations;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->locations = new AttendanceLocationModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $id = $this->locations->insert($data);
        $this->audit->log('create', 'attendance', 'attendance_location', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->locations->find($id);
        if (! $old) {
            throw new RuntimeException('Location not found.');
        }

        $this->locations->update($id, $data);
        $this->audit->log('update', 'attendance', 'attendance_location', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->locations->find($id);
        if (! $old) {
            throw new RuntimeException('Location not found.');
        }

        $this->locations->delete($id);
        $this->audit->log('delete', 'attendance', 'attendance_location', $id, $old, null);
    }
}
