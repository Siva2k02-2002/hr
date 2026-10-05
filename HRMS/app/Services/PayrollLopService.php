<?php

namespace App\Services;

/** Per-day rate = basis amount / working days for the period; amount = per-day rate * LOP days. */
class PayrollLopService
{
    public function calculate(float $basisAmount, float $workingDays, float $lopDays): float
    {
        if ($workingDays <= 0 || $lopDays <= 0) {
            return 0.0;
        }

        return round(($basisAmount / $workingDays) * $lopDays, 2);
    }
}
