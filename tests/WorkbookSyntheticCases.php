<?php

namespace Tests;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcAssessmentChoice;
use App\Models\WcDailyReview;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundAssessment;
use App\Models\WcWoundType;
use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

/**
 * Fixtures for the monthly case reports (spec 013).
 *
 * The workbook dataset is synthetic: it reproduces only the workbook's aggregate counts (research R9). No name,
 * ID or date of a real patient is used.
 */
trait WorkbookSyntheticCases
{
    private ?User $reportUser = null;

    private ?PerEmployee $reportEmployee = null;

    private int $patientSequence = 0;

    /**
     * Month × gender × diagnosis case counts of the clinic workbook (204 cases).
     *
     * @var list<array{0: string, 1: string, 2: string, 3: int}>
     */
    private const WORKBOOK_CELLS = [
        ['2025-11', 'FEMALE', 'Burn', 1], ['2025-11', 'FEMALE', 'DF', 23], ['2025-11', 'FEMALE', 'Inf Wound', 15],
        ['2025-11', 'FEMALE', 'SSI', 2], ['2025-11', 'FEMALE', 'bedsore PEU', 11],
        ['2025-11', 'MALE', 'Burn', 6], ['2025-11', 'MALE', 'DF', 23], ['2025-11', 'MALE', 'Inf Wound', 16],
        ['2025-11', 'MALE', 'Row Area', 5], ['2025-11', 'MALE', 'SSI', 2], ['2025-11', 'MALE', 'bedsore PEU', 21],
        ['2025-12', 'FEMALE', 'Burn', 2], ['2025-12', 'FEMALE', 'DF', 9], ['2025-12', 'FEMALE', 'Inf Wound', 9],
        ['2025-12', 'FEMALE', 'bedsore PEU', 10],
        ['2025-12', 'MALE', 'Burn', 2], ['2025-12', 'MALE', 'DF', 14], ['2025-12', 'MALE', 'Inf Wound', 15],
        ['2025-12', 'MALE', 'Row Area', 2], ['2025-12', 'MALE', 'SSI', 7], ['2025-12', 'MALE', 'bedsore PEU', 9],
    ];

    /**
     * Seeds the 204 synthetic workbook cases, including 6 readmissions: one patient registered twice in November
     * and five patients registered in November and again in December.
     */
    protected function seedWorkbookCases(): void
    {
        $entries = [];
        foreach (self::WORKBOOK_CELLS as [$month, $gender, $diagnosis, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $entries[] = ['month' => $month, 'gender' => $gender, 'diagnosis' => $diagnosis, 'patient' => null];
            }
        }

        $novemberMales = array_keys(array_filter($entries, fn (array $e): bool => $e['month'] === '2025-11' && $e['gender'] === 'MALE'));
        $decemberMales = array_keys(array_filter($entries, fn (array $e): bool => $e['month'] === '2025-12' && $e['gender'] === 'MALE'));

        $pairs = [[$novemberMales[0], $novemberMales[1]]];
        for ($i = 0; $i < 5; $i++) {
            $pairs[] = [$novemberMales[2 + $i], $decemberMales[$i]];
        }
        foreach ($pairs as $pairIndex => [$first, $second]) {
            $patient = $this->reportPatient(['MR_PAT_GENDER' => 'MALE', 'MR_PAT_FULL_NAME' => 'مريض متكرر '.($pairIndex + 1)]);
            $entries[$first]['patient'] = $patient;
            $entries[$second]['patient'] = $patient;
        }

        $dayInMonth = [];
        foreach ($entries as $index => $entry) {
            $isLaterOfSameMonthPair = $index === $pairs[0][1];
            $day = $isLaterOfSameMonthPair ? 28 : 1 + (($dayInMonth[$entry['month']] = ($dayInMonth[$entry['month']] ?? -1) + 1) % 27);

            $this->registerCase([
                'patient' => $entry['patient'] ?? $this->reportPatient(['MR_PAT_GENDER' => $entry['gender']]),
                'registeredAt' => CarbonImmutable::parse($entry['month'].'-'.str_pad((string) $day, 2, '0', STR_PAD_LEFT).' 09:00:00'),
                'wounds' => [['diagnosis' => $entry['diagnosis'], 'woundType' => 'Pressure']],
            ]);
        }
    }

    /**
     * A registration with its request and wounds.
     *
     * @param  array{
     *     patient?: MrPatient,
     *     registeredAt?: CarbonImmutable,
     *     department?: string,
     *     status?: string,
     *     dischargedAt?: ?CarbonImmutable,
     *     noComorbidities?: bool,
     *     pmhNotes?: ?string,
     *     wounds?: list<array{diagnosis: string, woundType: string, description?: ?string}>
     * }  $case
     */
    protected function registerCase(array $case = []): WcRegistration
    {
        $patient = $case['patient'] ?? $this->reportPatient();
        $registeredAt = $case['registeredAt'] ?? CarbonImmutable::parse('2025-11-10 09:00:00');

        $request = WcRequestReg::create([
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->reportEmployee()->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => $registeredAt,
        ]);

        $registration = WcRegistration::create([
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->reportDepartment($case['department'] ?? 'Medical Male')->WC_DEPT_CODE,
            'WC_REG_DATE' => $registeredAt,
            'WC_REG_NURSE_CD' => $this->reportEmployee()->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            'WC_REG_STATUS' => $case['status'] ?? 'ACTIVE',
            'WC_REG_DISCHARGED_AT' => $case['dischargedAt'] ?? null,
            'WC_REG_NO_COMORBIDITIES' => $case['noComorbidities'] ?? null,
            'WC_REG_PMH_NOTES' => $case['pmhNotes'] ?? null,
        ]);

        foreach ($case['wounds'] ?? [['diagnosis' => 'DF', 'woundType' => 'Pressure']] as $wound) {
            WcRegDiagnosis::create([
                'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
                'WC_RDIAG_DIAGNOSIS_CD' => WcDiagnosis::firstOrCreate(['WC_DIAG_NAME' => $wound['diagnosis']], ['WC_DIAG_IS_ACTIVE' => 1])->WC_DIAG_CODE,
                'WC_RDIAG_WOUND_TYPE_CD' => WcWoundType::firstOrCreate(['WC_WTYPE_NAME' => $wound['woundType']], ['WC_WTYPE_IS_ACTIVE' => 1])->WC_WTYPE_CODE,
                'WC_RDIAG_DESCRIPTION' => $wound['description'] ?? null,
                'WC_RDIAG_CLINICAL_EMP_CD' => $this->reportEmployee()->PER_EMP_CODE,
                'WC_RDIAG_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            ]);
        }

        return $registration;
    }

    /**
     * A structured assessment of a wound; requires the assessment choices to be seeded.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function assessWound(WcRegDiagnosis $wound, string $type, CarbonImmutable $assessedAt, array $attributes = []): WcWoundAssessment
    {
        return WcWoundAssessment::create([
            'WC_WA_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_WA_TYPE' => $type,
            'WC_WA_CLINICAL_EMP_CD' => $this->reportEmployee()->PER_EMP_CODE,
            'WC_WA_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            'WC_WA_ASSESSED_AT' => $assessedAt,
            'WC_WA_WOUND_STATUS_CD' => $this->reportChoice('WOUND_STATUS', $type === 'INITIAL' ? 'FIRST_VISIT' : 'IMPROVING'),
            'WC_WA_EXUDATE_AMOUNT_CD' => $this->reportChoice('EXUDATE_AMOUNT', 'DRY'),
            'WC_WA_ODOR_CD' => $this->reportChoice('ODOR', 'NONE'),
            'WC_WA_PAIN_SEVERITY_CD' => $this->reportChoice('PAIN_SEVERITY', 'NONE'),
            'WC_WA_PAIN_AT_DRESSING' => 0,
            'WC_WA_FOLLOW_UP_REQUIRED' => 1,
            ...$attributes,
        ]);
    }

    protected function reviewRegistration(WcRegistration $registration, CarbonImmutable $visitAt, string $overallStatus): WcDailyReview
    {
        return WcDailyReview::create([
            'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_DREV_VISIT_DATE' => $visitAt,
            'WC_DREV_NURSE_CD' => $this->reportEmployee()->PER_EMP_CODE,
            'WC_DREV_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => $overallStatus,
        ]);
    }

    /**
     * A patient; hospital-synced columns (national ID, admission date) are not mass assignable, hence forceFill.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function reportPatient(array $attributes = []): MrPatient
    {
        $this->patientSequence++;

        $patient = new MrPatient;
        $patient->forceFill([
            'MR_PAT_ID_NO' => 'SYN-'.str_pad((string) $this->patientSequence, 4, '0', STR_PAD_LEFT),
            'MR_PAT_FULL_NAME' => 'مريض اصطناعي '.$this->patientSequence,
            'MR_PAT_GENDER' => 'MALE',
            ...$attributes,
        ])->save();

        return $patient;
    }

    protected function reportDepartment(string $name): WcDepartment
    {
        return WcDepartment::firstOrCreate(['WC_DEPT_NAME' => $name], ['WC_DEPT_IS_ACTIVE' => 1]);
    }

    protected function reportChoice(string $category, string $key): string
    {
        return WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_KEY', $key)
            ->valueOrFail('WC_ACH_CODE');
    }

    protected function reportUser(): User
    {
        return $this->reportUser ??= User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'report_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);
    }

    protected function reportEmployee(): PerEmployee
    {
        if ($this->reportEmployee === null) {
            $this->reportEmployee = PerEmployee::create([
                'PER_EMP_FULL_NAME' => 'ممرض التقارير',
            ]);

            $this->reportUser()->update(['PER_USER_EMP_CD' => $this->reportEmployee->PER_EMP_CODE]);
        }

        return $this->reportEmployee;
    }

    /**
     * Cookies that sign the report user in through the hospital JWT middleware.
     *
     * @return array<string, string>
     */
    protected function reportCookies(): array
    {
        $token = JWT::encode([
            'sub' => $this->reportUser()->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], config('services.hospital_auth.jwt_secret'), 'HS256');

        return ['hospital_token' => $token];
    }
}
