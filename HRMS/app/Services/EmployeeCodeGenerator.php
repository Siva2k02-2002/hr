<?php

namespace App\Services;

use RuntimeException;

/**
 * Generates the next Employee Code (e.g. EMP000001) atomically. Locks the
 * single company_settings row with SELECT ... FOR UPDATE inside a
 * transaction so two simultaneous "Add Employee" submissions can never
 * reserve the same number — the running sequence is never reused, and
 * never falls back to the DB auto-increment id (per the spec).
 */
class EmployeeCodeGenerator
{
    public function next(): string
    {
        $db = service('tenantContext')->db();
        $db->transStart();

        $row = $db->query(
            'SELECT id, employee_code_prefix, employee_code_next_seq FROM company_settings ORDER BY id ASC LIMIT 1 FOR UPDATE'
        )->getRowArray();

        if (! $row) {
            $db->transRollback();

            throw new RuntimeException('Company settings row is missing — cannot generate an employee code.');
        }

        $seq  = (int) $row['employee_code_next_seq'];
        $code = $row['employee_code_prefix'] . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

        $db->table('company_settings')->where('id', $row['id'])->update(['employee_code_next_seq' => $seq + 1]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to reserve the next employee code.');
        }

        return $code;
    }
}
