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

    /**
     * English display name from the clinical glossary (spec 014).
     */
    public function label(): string
    {
        return __('clinical.case_outcomes.'.$this->value);
    }
}
