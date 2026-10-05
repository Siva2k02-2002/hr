<?php

namespace App\Models;

use CodeIgniter\Model;

class LeavePolicyModel extends Model
{
    protected $table          = 'leave_policies';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name', 'description', 'branch_id', 'department_id', 'designation_id', 'employment_type', 'employee_id',
        'is_default', 'effective_from', 'effective_to', 'priority', 'status', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'name' => 'required|max_length[150]',
    ];

    /**
     * Most-specific-wins resolution. A policy row matches an employee if every one of
     * its 5 scope columns is either NULL (applies to everyone on that dimension) or
     * equal to the employee's value — a policy can combine dimensions (e.g. "Designation
     * X AND Branch Y"), so this fetches every dimension-compatible row in one query and
     * ranks them in PHP, rather than an early-return per-tier scan (which would wrongly
     * match a multi-scoped policy against an employee who only satisfies one of its
     * dimensions). Specificity is weighted so one more-specific dimension always beats
     * any number of less-specific ones; `priority` breaks ties within equal specificity.
     */
    public function resolveFor(array $employee): ?array
    {
        $now = date('Y-m-d');

        $candidates = $this
            ->where('status', 'active')
            ->groupStart()->where('effective_from', null)->orWhere('effective_from <=', $now)->groupEnd()
            ->groupStart()->where('effective_to', null)->orWhere('effective_to >=', $now)->groupEnd()
            ->groupStart()->where('employee_id', null)->orWhere('employee_id', $employee['id'])->groupEnd()
            ->groupStart()->where('designation_id', null)->orWhere('designation_id', $employee['designation_id'] ?? 0)->groupEnd()
            ->groupStart()->where('department_id', null)->orWhere('department_id', $employee['department_id'] ?? 0)->groupEnd()
            ->groupStart()->where('branch_id', null)->orWhere('branch_id', $employee['branch_id'] ?? 0)->groupEnd()
            ->groupStart()->where('employment_type', null)->orWhere('employment_type', $employee['employment_type'] ?? '')->groupEnd()
            ->findAll();

        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (array $a, array $b) => $this->specificity($b) <=> $this->specificity($a) ?: ((int) $b['priority'] <=> (int) $a['priority']));

        return $candidates[0];
    }

    private function specificity(array $policy): int
    {
        return ($policy['employee_id'] !== null ? 16 : 0)
            + ($policy['designation_id'] !== null ? 8 : 0)
            + ($policy['department_id'] !== null ? 4 : 0)
            + ($policy['branch_id'] !== null ? 2 : 0)
            + ($policy['employment_type'] !== null ? 1 : 0);
    }
}
