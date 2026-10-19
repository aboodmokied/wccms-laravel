<?php

namespace App\Services\Reports;

/**
 * Outcome of a clinic case as printed in the monthly reports.
 */
enum CaseOutcome: string
{
    case Ongoing = 'ONGOING';
    case Discharged = 'DISCHARGED';
    case Death = 'DEATH';
    case HospitalTransfer = 'HOSPITAL_TRANSFER';

    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'يستمر العلاج',
            self::Discharged => 'خروج',
            self::Death => 'وفاة',
            self::HospitalTransfer => 'تحويل للمستشفى',
        };
    }
}
