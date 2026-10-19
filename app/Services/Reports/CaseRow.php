<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;

/**
 * One clinic registration as a row of the monthly case reports.
 *
 * Wound-level and treatment fields hold the distinct values of the whole case, in first-seen order.
 */
final readonly class CaseRow
{
    /**
     * @param  list<string>  $pastMedicalHistory
     * @param  list<string>  $diagnoses
     * @param  list<string>  $woundLocations
     * @param  list<string>  $woundTypes
     * @param  list<string>  $dressings
     * @param  list<string>  $medications
     * @param  list<string>  $procedures
     */
    public function __construct(
        public int $number,
        public string $registrationCode,
        public CarbonImmutable $registeredAt,
        public string $patientCode,
        public string $patientName,
        public ?string $idNumber,
        public ?string $gender,
        public ?int $birthYear,
        public ?CarbonImmutable $hospitalAdmissionDate,
        public ?string $department,
        public array $pastMedicalHistory,
        public array $diagnoses,
        public array $woundLocations,
        public array $woundTypes,
        public array $dressings,
        public array $medications,
        public array $procedures,
        public int $photoCount,
        public ?CarbonImmutable $dischargeDate,
        public CaseOutcome $outcome,
    ) {}

    /**
     * The calendar month the case belongs to (first day, start of day).
     */
    public function month(): CarbonImmutable
    {
        return $this->registeredAt->startOfMonth();
    }
}
