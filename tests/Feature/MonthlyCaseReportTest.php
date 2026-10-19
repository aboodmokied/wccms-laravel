<?php

namespace Tests\Feature;

use App\Models\WcAssessmentFinding;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDressing;
use App\Models\WcProcedure;
use App\Models\WcWoundPhoto;
use App\Services\Reports\CaseOutcome;
use App\Services\Reports\CaseRow;
use Carbon\CarbonImmutable;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\WorkbookSyntheticCases;

class MonthlyCaseReportTest extends TestCase
{
    use RefreshDatabase;
    use WorkbookSyntheticCases;

    public function test_unauthenticated_request_redirects_to_hospital_login(): void
    {
        $response = $this->get(route('reports.monthly.cases'));

        $response->assertRedirect(config('services.hospital_auth.login_url', '/mock-hospital-auth'));
    }

    public function test_lists_every_case_registered_in_the_month_numbered_in_registration_order(): void
    {
        $this->seedWorkbookCases();

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $cases = $this->casesOf($response);
        $this->assertCount(125, $cases);
        $this->assertSame(range(1, 125), $cases->pluck('number')->all());
        $this->assertSame(
            $cases->pluck('registeredAt')->sort()->values()->map->toDateTimeString()->all(),
            $cases->pluck('registeredAt')->map->toDateTimeString()->all(),
        );
        $this->assertTrue($cases->every(fn (CaseRow $case): bool => $case->registeredAt->format('Y-m') === '2025-11'));
    }

    public function test_case_with_two_wounds_is_one_row_listing_both_diagnoses(): void
    {
        $this->registerCase(['wounds' => [
            ['diagnosis' => 'DF', 'woundType' => 'Surgical'],
            ['diagnosis' => 'bedsore PEU', 'woundType' => 'Pressure'],
        ]]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $cases = $this->casesOf($response);
        $this->assertCount(1, $cases);
        $this->assertSame(['DF', 'bedsore PEU'], $cases->first()->diagnoses);
        $response->assertSee('DF, bedsore PEU');
    }

    public function test_lists_each_dressing_used_during_the_case_once_in_first_seen_order(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $gauze = WcDressing::create(['WC_DRESS_NAME' => 'Gauze DSG', 'WC_DRESS_IS_ACTIVE' => 1]);
        $hydrogel = WcDressing::create(['WC_DRESS_NAME' => 'hydrogel', 'WC_DRESS_IS_ACTIVE' => 1]);
        $registration = $this->registerCase();
        $wound = $registration->diagnoses()->firstOrFail();
        $this->assessWound($wound, 'INITIAL', CarbonImmutable::parse('2025-11-10 09:00'), ['WC_WA_DRESSING_CD' => $gauze->WC_DRESS_CODE]);
        $review = $this->reviewRegistration($registration, CarbonImmutable::parse('2025-11-12 09:00'), 'IMPROVED_STAY');
        WcDailyReviewDetail::create([
            'WC_REVD_REVIEW_CD' => $review->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $hydrogel->WC_DRESS_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
        ]);
        $secondReview = $this->reviewRegistration($registration, CarbonImmutable::parse('2025-11-14 09:00'), 'IMPROVED_STAY');
        WcDailyReviewDetail::create([
            'WC_REVD_REVIEW_CD' => $secondReview->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $gauze->WC_DRESS_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
        ]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $this->assertSame(['Gauze DSG', 'hydrogel'], $this->casesOf($response)->first()->dressings);
    }

    public function test_department_filter_keeps_only_cases_of_the_selected_departments(): void
    {
        foreach (['Orthopedic Male', 'Orthopedic Female', 'Special Male', 'Special Female', 'Medical Male', 'CCU'] as $department) {
            $this->registerCase(['department' => $department]);
        }
        $building = collect(['Orthopedic Male', 'Orthopedic Female', 'Special Male', 'Special Female'])
            ->map(fn (string $name): string => $this->reportDepartment($name)->WC_DEPT_CODE)
            ->all();

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11', 'departments' => $building]), [], $this->reportCookies());

        $this->assertSame(
            ['Orthopedic Female', 'Orthopedic Male', 'Special Female', 'Special Male'],
            $this->casesOf($response)->pluck('department')->sort()->values()->all(),
        );
    }

    public function test_active_case_has_no_discharge_date_and_continues_treatment(): void
    {
        $this->registerCase(['status' => 'ACTIVE']);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $case = $this->casesOf($response)->first();
        $this->assertNull($case->dischargeDate);
        $this->assertSame(CaseOutcome::Ongoing, $case->outcome);
        $response->assertSee('Ongoing Treatment');
    }

    /**
     * @return array<string, array{0: ?string, 1: CaseOutcome}>
     */
    public static function closingReviews(): array
    {
        return [
            'death review' => ['DEATH', CaseOutcome::Death],
            'worsened admission review' => ['WORSENED_ADMISSION', CaseOutcome::HospitalTransfer],
            'improved discharge review' => ['IMPROVED_DISCHARGE', CaseOutcome::Discharged],
            'manual discharge without review' => [null, CaseOutcome::Discharged],
        ];
    }

    #[DataProvider('closingReviews')]
    public function test_closed_case_outcome_follows_the_latest_review(?string $closingStatus, CaseOutcome $expected): void
    {
        $registration = $this->registerCase([
            'status' => 'DISCHARGED',
            'dischargedAt' => CarbonImmutable::parse('2026-01-05 14:30'),
        ]);
        if ($closingStatus !== null) {
            $this->reviewRegistration($registration, CarbonImmutable::parse('2025-11-20 09:00'), 'IMPROVED_STAY');
            $this->reviewRegistration($registration, CarbonImmutable::parse('2026-01-05 09:00'), $closingStatus);
        }

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $case = $this->casesOf($response)->first();
        $this->assertSame($expected, $case->outcome);
        $this->assertSame('2026-01-05', $case->dischargeDate?->toDateString());
    }

    public function test_procedures_include_the_registration_procedure_assessment_procedures_and_a_swab(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $debridement = WcProcedure::create(['WC_PROC_NAME' => 'debridement', 'WC_PROC_IS_ACTIVE' => 1]);
        $registration = $this->registerCase();
        $registration->update(['WC_REG_PROCEDURE_CD' => $debridement->WC_PROC_CODE]);
        $wound = $registration->diagnoses()->firstOrFail();
        $this->assessWound($wound, 'INITIAL', CarbonImmutable::parse('2025-11-10 09:00'), [
            'WC_WA_PROCEDURE_CD' => $debridement->WC_PROC_CODE,
            'WC_WA_SWAB_TAKEN' => 1,
        ]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $this->assertSame(['debridement', 'swap culture'], $this->casesOf($response)->first()->procedures);
    }

    public function test_structured_case_shows_location_tissue_findings_and_photo_count(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $registration = $this->registerCase(['wounds' => [['diagnosis' => 'DF', 'woundType' => 'Pressure', 'description' => 'وصف قديم']]]);
        $wound = $registration->diagnoses()->firstOrFail();
        $initial = $this->assessWound($wound, 'INITIAL', CarbonImmutable::parse('2025-11-10 09:00'), [
            'WC_WA_LOCATION_CD' => $this->reportChoice('LOCATION', 'FOOT'),
            'WC_WA_LATERALITY_CD' => $this->reportChoice('LATERALITY', 'RIGHT'),
            'WC_WA_LOCATION_NOTES' => 'الكعب',
        ]);
        foreach ([['WOUND_BED', 'NECROTIC'], ['WOUND_MARGIN', 'POCKETING'], ['WOUND_MARGIN', 'NONE_OBSERVED']] as [$category, $key]) {
            WcAssessmentFinding::create(['WC_AF_ASSESSMENT_CD' => $initial->WC_WA_CODE, 'WC_AF_CHOICE_CD' => $this->reportChoice($category, $key)]);
        }
        foreach ([1, 2] as $photo) {
            WcWoundPhoto::create([
                'WC_PHOTO_CODE' => (string) Str::uuid(),
                'WC_PHOTO_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
                'WC_PHOTO_STAGE' => 'INITIAL',
                'WC_PHOTO_PATH' => "wounds/photo-{$photo}.jpg",
                'WC_PHOTO_TAKEN_AT' => CarbonImmutable::parse('2025-11-10 09:00'),
                'WC_PHOTO_NURSE_CD' => $this->reportEmployee()->PER_EMP_CODE,
                'WC_PHOTO_ENTRY_USER_CD' => $this->reportUser()->PER_USER_CODE,
            ]);
        }

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $case = $this->casesOf($response)->first();
        $this->assertSame(['Foot - Right - الكعب'], $case->woundLocations);
        $this->assertSame(['Pressure (Necrotic, Pocketing)'], $case->woundTypes);
        $this->assertSame(2, $case->photoCount);
    }

    public function test_case_recorded_before_structured_assessments_falls_back_to_legacy_fields(): void
    {
        $gauze = WcDressing::create(['WC_DRESS_NAME' => 'Gauze DSG', 'WC_DRESS_IS_ACTIVE' => 1]);
        $registration = $this->registerCase([
            'pmhNotes' => 'DM',
            'wounds' => [['diagnosis' => 'DF', 'woundType' => 'Pressure', 'description' => 'Rt foot']],
        ]);
        $review = $this->reviewRegistration($registration, CarbonImmutable::parse('2025-11-12 09:00'), 'IMPROVED_STAY');
        WcDailyReviewDetail::create([
            'WC_REVD_REVIEW_CD' => $review->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $registration->diagnoses()->firstOrFail()->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $gauze->WC_DRESS_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
        ]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $case = $this->casesOf($response)->first();
        $this->assertSame(['Rt foot'], $case->woundLocations);
        $this->assertSame(['Pressure'], $case->woundTypes);
        $this->assertSame(['Gauze DSG'], $case->dressings);
        $this->assertSame(['DM'], $case->pastMedicalHistory);
    }

    public function test_shows_the_national_id_before_the_clinic_number_and_no_comorbidities_as_none(): void
    {
        $this->registerCase([
            'patient' => $this->reportPatient(['MR_PAT_ID_NO' => 'PAT-1001', 'MR_PAT_NATIONAL_ID' => '427408844', 'MR_PAT_DOB' => '1969-04-02']),
            'registeredAt' => CarbonImmutable::parse('2025-11-10 09:00'),
            'noComorbidities' => true,
        ]);
        $this->registerCase([
            'patient' => $this->reportPatient(['MR_PAT_ID_NO' => 'PAT-1002']),
            'registeredAt' => CarbonImmutable::parse('2025-11-11 09:00'),
        ]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $cases = $this->casesOf($response);
        $this->assertSame(['427408844', 'PAT-1002'], $cases->pluck('idNumber')->all());
        $this->assertSame(1969, $cases->first()->birthYear);
        $this->assertSame(['No'], $cases->first()->pastMedicalHistory);
    }

    public function test_month_without_cases_shows_the_empty_state(): void
    {
        $this->registerCase(['registeredAt' => CarbonImmutable::parse('2025-12-03 09:00')]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $response->assertSee('لا توجد حالات في هذا الشهر.');
    }

    public function test_defaults_to_the_current_month(): void
    {
        $this->travelTo(CarbonImmutable::parse('2025-12-15 10:00'));
        $this->registerCase(['registeredAt' => CarbonImmutable::parse('2025-12-03 09:00')]);
        $this->registerCase(['registeredAt' => CarbonImmutable::parse('2025-11-03 09:00')]);

        $response = $this->call('GET', route('reports.monthly.cases'), [], $this->reportCookies());

        $this->assertSame(['2025-12'], $this->casesOf($response)->map(fn (CaseRow $case): string => $case->registeredAt->format('Y-m'))->all());
    }

    public function test_rejects_an_invalid_month_with_an_arabic_message(): void
    {
        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-13']), [], $this->reportCookies());

        $response->assertRedirect(route('reports.monthly.cases'));
        $response->assertSessionHasErrors(['month' => 'صيغة الشهر غير صحيحة.']);
    }

    public function test_rejects_an_unknown_department_with_an_arabic_message(): void
    {
        $response = $this->call('GET', route('reports.monthly.cases', ['departments' => ['not-a-department']]), [], $this->reportCookies());

        $response->assertSessionHasErrors(['departments.0' => 'القسم المحدد غير موجود.']);
    }

    public function test_table_uses_the_workbook_headers_and_english_values(): void
    {
        $this->registerCase([
            'patient' => $this->reportPatient(['MR_PAT_GENDER' => 'FEMALE']),
            'registeredAt' => CarbonImmutable::parse('2025-11-03 09:00'),
            'status' => 'DISCHARGED',
            'dischargedAt' => CarbonImmutable::parse('2025-11-20 10:00'),
        ]);
        $registration = $this->registerCase([
            'registeredAt' => CarbonImmutable::parse('2025-11-04 09:00'),
            'status' => 'DISCHARGED',
            'dischargedAt' => CarbonImmutable::parse('2025-11-25 10:00'),
        ]);
        $this->reviewRegistration($registration, CarbonImmutable::parse('2025-11-25 09:00'), 'DEATH');

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $response->assertSeeInOrder([
            'NO', 'Pt Name', 'MONTHS', 'ID', 'Gender', 'DoB', 'Diagnosis', 'Discussion', 'DoA', 'PMH', 'Type of Wound',
            'Type of DSG', 'Medication', 'Procedure', 'Department', 'Picture', 'DISCHARGE', 'Comment',
        ]);
        $response->assertSeeInOrder(['Nov -2025', 'Female', 'Discharged', 'Nov -2025', 'Male', 'Death']);
    }

    public function test_escapes_patient_names_in_the_table(): void
    {
        $this->registerCase(['patient' => $this->reportPatient(['MR_PAT_FULL_NAME' => "O'Brien <script>alert('x')</script>"])]);

        $response = $this->call('GET', route('reports.monthly.cases', ['month' => '2025-11']), [], $this->reportCookies());

        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee("<script>alert('x')</script>", false);
    }

    /**
     * @return Collection<int, CaseRow>
     */
    private function casesOf($response): Collection
    {
        return $response->viewData('cases');
    }
}
