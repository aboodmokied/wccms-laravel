<?php

namespace App\Services\Reports;

use App\Models\WcAssessmentFinding;
use App\Models\WcDailyReview;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcWoundAssessment;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Read-only monthly case reports equivalent to the clinic workbook (spec 013).
 *
 * Rows and counts are built in PHP from eager-loaded registrations, so the same code runs on SQLite, MySQL and
 * Oracle (research R1). A case belongs to the month of its clinic registration date (research R2).
 */
class MonthlyCaseReportService
{
    private const TISSUE_FINDING_CATEGORIES = ['WOUND_BED', 'WOUND_MARGIN'];

    private const NO_FINDING_KEYS = ['NONE_OBSERVED'];

    private const IN_LIST_CHUNK = 500;

    /**
     * Cases registered from the first day of $fromMonth to the last day of $toMonth, numbered from 1.
     *
     * @param  list<string>  $departmentCodes  empty = every department
     * @return Collection<int, CaseRow>
     */
    public function cases(CarbonInterface $fromMonth, CarbonInterface $toMonth, array $departmentCodes = []): Collection
    {
        [$start, $end] = $this->bounds($fromMonth, $toMonth);

        $query = WcRegistration::query()
            ->where('WC_REG_DATE', '>=', $start)
            ->where('WC_REG_DATE', '<', $end)
            ->orderBy('WC_REG_DATE')
            ->orderBy('WC_REG_CODE');

        if ($departmentCodes !== []) {
            $query->whereIn('WC_REG_DEPARTMENT_CD', $departmentCodes);
        }

        return $this->withCaseRelations($query->get())
            ->values()
            ->map(fn (WcRegistration $registration, int $index): CaseRow => $this->toRow($registration, $index + 1));
    }

    /**
     * Case counts per month, gender and diagnosis over the month range.
     *
     * @param  list<string>  $departmentCodes
     * @return array{
     *     perMonth: list<array{month: CarbonImmutable, count: int}>,
     *     perGender: list<array{label: string, count: int}>,
     *     perDiagnosis: list<array{label: string, count: int}>,
     *     totalCases: int
     * }
     */
    public function summary(CarbonInterface $fromMonth, CarbonInterface $toMonth, array $departmentCodes = []): array
    {
        return $this->summarize($this->cases($fromMonth, $toMonth, $departmentCodes), $fromMonth, $toMonth);
    }

    /**
     * The summary of cases already loaded for the same month range.
     *
     * @param  Collection<int, CaseRow>  $cases
     * @return array{
     *     perMonth: list<array{month: CarbonImmutable, count: int}>,
     *     perGender: list<array{label: string, count: int}>,
     *     perDiagnosis: list<array{label: string, count: int}>,
     *     totalCases: int
     * }
     */
    public function summarize(Collection $cases, CarbonInterface $fromMonth, CarbonInterface $toMonth): array
    {
        [$start, $end] = $this->bounds($fromMonth, $toMonth);

        $casesPerMonth = $cases->countBy(fn (CaseRow $case): string => $case->registeredAt->format('Y-m'));

        $perMonth = collect(CarbonPeriod::create($start, '1 month', $end->subDay()))
            ->map(fn (CarbonInterface $month): array => [
                'month' => CarbonImmutable::instance($month)->startOfMonth(),
                'count' => $casesPerMonth->get($month->format('Y-m'), 0),
            ])
            ->values()
            ->all();

        $perGender = collect([__('clinical.genders.MALE'), __('clinical.genders.FEMALE')])
            ->map(fn (string $gender): array => [
                'label' => $gender,
                'count' => $cases->where('gender', $gender)->count(),
            ]);

        $unspecified = $cases->whereNull('gender')->count();
        if ($unspecified > 0) {
            $perGender->push(['label' => __('clinical.genders.UNSPECIFIED'), 'count' => $unspecified]);
        }

        $perDiagnosis = $cases
            ->flatMap(fn (CaseRow $case): array => $case->diagnoses)
            ->countBy()
            ->map(fn (int $count, string $label): array => ['label' => $label, 'count' => $count])
            ->sort(fn (array $a, array $b): int => [$b['count'], $a['label']] <=> [$a['count'], $b['label']])
            ->values()
            ->all();

        return [
            'perMonth' => $perMonth,
            'perGender' => $perGender->all(),
            'perDiagnosis' => $perDiagnosis,
            'totalCases' => $cases->count(),
        ];
    }

    /**
     * Cases of the range whose patient had any earlier registration, with that registration's date and outcome.
     *
     * @param  list<string>  $departmentCodes
     * @return Collection<int, ReadmissionRow>
     */
    public function readmissions(CarbonInterface $fromMonth, CarbonInterface $toMonth, array $departmentCodes = []): Collection
    {
        return $this->readmissionsAmong($this->cases($fromMonth, $toMonth, $departmentCodes));
    }

    /**
     * The readmissions among cases already loaded.
     *
     * @param  Collection<int, CaseRow>  $cases
     * @return Collection<int, ReadmissionRow>
     */
    public function readmissionsAmong(Collection $cases): Collection
    {
        if ($cases->isEmpty()) {
            return collect();
        }

        $latestCase = $cases->max(fn (CaseRow $case): CarbonImmutable => $case->registeredAt);

        $history = $cases->pluck('patientCode')->unique()->chunk(self::IN_LIST_CHUNK)
            ->flatMap(fn (Collection $patientCodes): EloquentCollection => WcRegistration::query()
                ->whereIn('WC_REG_PATIENT_CD', $patientCodes->values()->all())
                ->where('WC_REG_DATE', '<=', $latestCase)
                ->with('dailyReviews')
                ->get())
            ->groupBy('WC_REG_PATIENT_CD');

        return $cases
            ->map(function (CaseRow $case) use ($history): ?ReadmissionRow {
                $previous = $history->get($case->patientCode, collect())
                    ->filter(fn (WcRegistration $registration): bool => $this->isEarlier($registration, $case))
                    ->sortBy([['WC_REG_DATE', 'asc'], ['WC_REG_CODE', 'asc']])
                    ->last();

                if ($previous === null) {
                    return null;
                }

                return new ReadmissionRow(
                    $case,
                    $this->immutable($previous->WC_REG_DATE),
                    $this->outcome($previous),
                );
            })
            ->filter()
            ->values();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable} [first day of $fromMonth, first day after $toMonth)
     */
    private function bounds(CarbonInterface $fromMonth, CarbonInterface $toMonth): array
    {
        $start = CarbonImmutable::instance($fromMonth)->startOfMonth();
        $end = CarbonImmutable::instance($toMonth)->startOfMonth()->addMonth();

        return [$start, $end];
    }

    /**
     * @param  EloquentCollection<int, WcRegistration>  $registrations
     * @return EloquentCollection<int, WcRegistration>
     */
    private function withCaseRelations(EloquentCollection $registrations): EloquentCollection
    {
        return $registrations->load([
            'patient',
            'department',
            'procedure',
            'pmhConditions',
            'diagnoses' => fn ($query) => $query->withCount('woundPhotos as WOUND_PHOTOS_COUNT'),
            'diagnoses.diagnosis',
            'diagnoses.woundType',
            'diagnoses.assessments.dressing',
            'diagnoses.assessments.medication',
            'diagnoses.assessments.procedure',
            'diagnoses.assessments.location',
            'diagnoses.assessments.laterality',
            'diagnoses.assessments.findings.choice',
            'dailyReviews.details.dressing',
            'dailyReviews.details.medication',
        ]);
    }

    private function toRow(WcRegistration $registration, int $number): CaseRow
    {
        $patient = $registration->patient;
        $wounds = $registration->diagnoses
            ->sortBy([
                fn (WcRegDiagnosis $a, WcRegDiagnosis $b): int => (string) $this->initialAssessment($a)?->WC_WA_ASSESSED_AT <=> (string) $this->initialAssessment($b)?->WC_WA_ASSESSED_AT,
                fn (WcRegDiagnosis $a, WcRegDiagnosis $b): int => (string) $a->diagnosis?->WC_DIAG_NAME <=> (string) $b->diagnosis?->WC_DIAG_NAME,
                fn (WcRegDiagnosis $a, WcRegDiagnosis $b): int => $a->WC_RDIAG_CODE <=> $b->WC_RDIAG_CODE,
            ])
            ->values();
        $assessments = $wounds
            ->flatMap(fn (WcRegDiagnosis $wound): Collection => $wound->assessments)
            ->sortBy(fn (WcWoundAssessment $assessment): string => ($assessment->WC_WA_TYPE === 'INITIAL' ? '0' : '1').$assessment->WC_WA_ASSESSED_AT?->toDateTimeString())
            ->values();
        $reviewDetails = $registration->dailyReviews
            ->sortBy([['WC_DREV_VISIT_DATE', 'asc'], ['WC_DREV_CODE', 'asc']])
            ->flatMap(fn (WcDailyReview $review): Collection => $review->details);

        $isClosed = $registration->WC_REG_STATUS === 'DISCHARGED';
        $closedAt = $registration->WC_REG_DISCHARGED_AT ?? $registration->WC_REG_DISCHARGE_DATE;

        return new CaseRow(
            number: $number,
            registrationCode: $registration->WC_REG_CODE,
            registeredAt: $this->immutable($registration->WC_REG_DATE),
            patientCode: $registration->WC_REG_PATIENT_CD,
            patientName: (string) $patient?->MR_PAT_FULL_NAME,
            idNumber: $this->firstFilled($patient?->MR_PAT_NATIONAL_ID, $patient?->MR_PAT_ID_NO),
            gender: $this->gender($patient?->MR_PAT_GENDER),
            birthYear: $patient?->MR_PAT_DOB ? $this->immutable($patient->MR_PAT_DOB)->year : null,
            hospitalAdmissionDate: $patient?->MR_PAT_HOSPITAL_ADMISSION_DATE
                ? $this->immutable($patient->MR_PAT_HOSPITAL_ADMISSION_DATE)->startOfDay()
                : null,
            department: $registration->department?->WC_DEPT_NAME,
            pastMedicalHistory: $this->pastMedicalHistory($registration),
            diagnoses: $this->distinct($wounds->map(fn (WcRegDiagnosis $wound): ?string => $wound->diagnosis?->WC_DIAG_NAME)),
            woundLocations: $this->distinct($wounds->map(fn (WcRegDiagnosis $wound): ?string => $this->woundLocation($wound))),
            woundTypes: $this->distinct($wounds->map(fn (WcRegDiagnosis $wound): ?string => $this->woundType($wound))),
            dressings: $this->distinct([
                ...$assessments->map(fn (WcWoundAssessment $assessment): ?string => $assessment->dressing?->WC_DRESS_NAME),
                ...$reviewDetails->map(fn ($detail): ?string => $detail->dressing?->WC_DRESS_NAME),
            ]),
            medications: $this->distinct([
                ...$assessments->map(fn (WcWoundAssessment $assessment): ?string => $assessment->medication?->WC_MED_NAME),
                ...$reviewDetails->map(fn ($detail): ?string => $detail->medication?->WC_MED_NAME),
            ]),
            procedures: $this->distinct([
                $registration->procedure?->WC_PROC_NAME,
                ...$assessments->map(fn (WcWoundAssessment $assessment): ?string => $assessment->procedure?->WC_PROC_NAME),
                $assessments->contains(fn (WcWoundAssessment $assessment): bool => (bool) $assessment->WC_WA_SWAB_TAKEN)
                    ? __('clinical.swab_procedure')
                    : null,
            ]),
            photoCount: (int) $wounds->sum('WOUND_PHOTOS_COUNT'),
            dischargeDate: $isClosed && $closedAt ? $this->immutable($closedAt)->startOfDay() : null,
            outcome: $this->outcome($registration),
        );
    }

    /**
     * Research R4: active → ongoing; otherwise the latest review decides death or hospital transfer, else discharge.
     */
    private function outcome(WcRegistration $registration): CaseOutcome
    {
        if ($registration->WC_REG_STATUS !== 'DISCHARGED') {
            return CaseOutcome::Ongoing;
        }

        $lastReview = $registration->dailyReviews
            ->sortBy([['WC_DREV_VISIT_DATE', 'asc'], ['WC_DREV_CODE', 'asc']])
            ->last();

        return match ($lastReview?->WC_DREV_OVERALL_STATUS) {
            'DEATH' => CaseOutcome::Death,
            'WORSENED_ADMISSION' => CaseOutcome::HospitalTransfer,
            default => CaseOutcome::Discharged,
        };
    }

    /**
     * @return list<string>
     */
    private function pastMedicalHistory(WcRegistration $registration): array
    {
        $conditions = $this->distinct($registration->pmhConditions->pluck('WC_PMH_NAME'));

        if ($conditions !== []) {
            return $conditions;
        }

        if ((bool) $registration->WC_REG_NO_COMORBIDITIES) {
            return [__('clinical.no_comorbidities')];
        }

        return $this->distinct([$registration->WC_REG_PMH_NOTES]);
    }

    private function woundLocation(WcRegDiagnosis $wound): ?string
    {
        $initial = $this->initialAssessment($wound);
        $parts = $this->distinct([
            $initial?->location?->displayLabel(),
            $initial?->laterality?->displayLabel(),
            $initial?->WC_WA_LOCATION_NOTES,
        ]);

        return $parts !== [] ? implode(' - ', $parts) : $this->firstFilled($wound->WC_RDIAG_DESCRIPTION);
    }

    private function woundType(WcRegDiagnosis $wound): ?string
    {
        $type = $this->firstFilled($wound->woundType?->WC_WTYPE_NAME);
        $findings = $this->distinct(
            ($this->initialAssessment($wound)?->findings ?? collect())
                ->filter(fn (WcAssessmentFinding $finding): bool => in_array($finding->choice?->WC_ACH_CATEGORY, self::TISSUE_FINDING_CATEGORIES, true)
                    && ! in_array($finding->choice?->WC_ACH_KEY, self::NO_FINDING_KEYS, true))
                ->sortBy([
                    fn (WcAssessmentFinding $a, WcAssessmentFinding $b): int => array_search($a->choice->WC_ACH_CATEGORY, self::TISSUE_FINDING_CATEGORIES, true)
                        <=> array_search($b->choice->WC_ACH_CATEGORY, self::TISSUE_FINDING_CATEGORIES, true),
                    fn (WcAssessmentFinding $a, WcAssessmentFinding $b): int => (int) $a->choice->WC_ACH_DISPLAY_ORDER <=> (int) $b->choice->WC_ACH_DISPLAY_ORDER,
                ])
                ->map(fn (WcAssessmentFinding $finding): ?string => $finding->choice?->displayLabel())
        );

        if ($findings === []) {
            return $type;
        }

        return trim(($type ?? '').' ('.implode(ReportFormat::LIST_SEPARATOR, $findings).')');
    }

    private function initialAssessment(WcRegDiagnosis $wound): ?WcWoundAssessment
    {
        return $wound->assessments->firstWhere('WC_WA_TYPE', 'INITIAL');
    }

    private function isEarlier(WcRegistration $registration, CaseRow $case): bool
    {
        $registeredAt = $this->immutable($registration->WC_REG_DATE);

        return $registeredAt->lessThan($case->registeredAt)
            || ($registeredAt->equalTo($case->registeredAt) && strcmp($registration->WC_REG_CODE, $case->registrationCode) < 0);
    }

    /**
     * Casted attributes are already dates; wrapping them is much cheaper than parsing them again.
     */
    private function immutable(DateTimeInterface|string $value): CarbonImmutable
    {
        return $value instanceof DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value);
    }

    private function gender(?string $gender): ?string
    {
        return match (mb_strtoupper(trim((string) $gender))) {
            'MALE', 'M' => __('clinical.genders.MALE'),
            'FEMALE', 'F' => __('clinical.genders.FEMALE'),
            default => null,
        };
    }

    private function firstFilled(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Non-empty trimmed strings, duplicates removed, first-seen order kept.
     *
     * @param  iterable<int, ?string>  $values
     * @return list<string>
     */
    private function distinct(iterable $values): array
    {
        return collect($values)
            ->map(fn (?string $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }
}
