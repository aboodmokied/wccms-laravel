<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DailyReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $nurseUser;

    private PerEmployee $nurseEmployee;

    private WcDepartment $department;

    private WcRegistration $registration;

    private WcRegDiagnosis $regDiagnosis;

    private WcDressing $dressing;

    private WcMedication $medication;

    private string $jwtToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WcAssessmentChoiceSeeder::class);

        $this->nurseUser = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'test_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->nurseEmployee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'ممرض تقييم يومي',
            'PER_EMP_USER_CD' => $this->nurseUser->PER_USER_CODE,
        ]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم الجراحة',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $this->dressing = WcDressing::create([
            'WC_DRESS_CODE' => (string) Str::uuid(),
            'WC_DRESS_NAME' => 'ضمادة رغوية (Foam Dressing)',
            'WC_DRESS_IS_ACTIVE' => 1,
        ]);

        $this->medication = WcMedication::create([
            'WC_MED_CODE' => (string) Str::uuid(),
            'WC_MED_NAME' => 'مرهم سيلفر سلفاديازين',
            'WC_MED_IS_ACTIVE' => 1,
        ]);

        $patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '100200300',
            'MR_PAT_FULL_NAME' => 'أحمد محمود علي',
            'MR_PAT_GENDER' => 'MALE',
        ]);

        $request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->nurseEmployee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $this->registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurseEmployee->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'قدم سكرية (Diabetic Foot)',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        $diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'قرحة القدم السكرية',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        $this->regDiagnosis = WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $woundType->WC_WTYPE_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->nurseEmployee->PER_EMP_CODE,
            'WC_RDIAG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
        ]);

        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');
        $this->jwtToken = JWT::encode([
            'sub' => $this->nurseUser->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $secret, 'HS256');
    }

    public function test_daily_review_entry_form_opens_directly_for_active_registration(): void
    {
        $response = $this->call(
            'GET',
            "/reviews/daily/{$this->registration->WC_REG_CODE}/create",
            [],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertOk();
        $response->assertViewIs('reviews.daily_create');
        $response->assertSee('إدخال مراجعة يومية');
        $response->assertSee('أحمد محمود علي');
        $response->assertSee('عرض التاريخ الصحي');
    }

    public function test_retired_daily_review_workspace_redirects_to_patient_health_history(): void
    {
        $response = $this->call(
            'GET',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect(route('patients.show', $this->registration->WC_REG_PATIENT_CD));
    }

    /**
     * US2: Multi-wound daily review creation stores header and detail records
     * with dual audit tracking (nurse vs entry user).
     */
    public function test_can_create_multi_wound_daily_review_with_dual_audit(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'general_notes' => 'ملاحظة اختبارية',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'dressing_code' => $this->dressing->WC_DRESS_CODE,
                        'medication_code' => $this->medication->WC_MED_CODE,
                        'clinical_observation' => 'تكون أنسجة حبيبانية',
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // Verify header record with dual audit
        $this->assertDatabaseHas('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_NURSE_CD' => $this->nurseEmployee->PER_EMP_CODE,
            'WC_DREV_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY',
            'WC_DREV_GENERAL_NOTES' => 'ملاحظة اختبارية',
        ]);

        // Verify detail record
        $this->assertDatabaseHas('wc_review_detail_tb', [
            'WC_DREVD_REG_DIAGNOSIS_CD' => $this->regDiagnosis->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $this->dressing->WC_DRESS_CODE,
            'WC_DREVD_MEDICATION_CD' => $this->medication->WC_MED_CODE,
            'WC_DREVD_WOUND_DESCRIPTION' => 'تكون أنسجة حبيبانية',
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
        ]);

        // Registration should stay ACTIVE
        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);
    }

    /**
     * US3: Automatic discharge when overall outcome is IMPROVED_DISCHARGE.
     */
    public function test_improved_discharge_transitions_registration_to_discharged(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'IMPROVED_DISCHARGE',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'IMPROVED_DISCHARGE',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'DISCHARGED',
            'WC_REG_DISCHARGED_BY_EMP_CD' => $this->nurseEmployee->PER_EMP_CODE,
        ]);
    }

    /**
     * US3: IMPROVED_SCHEDULED keeps the registration active and creates a pending appointment.
     */
    public function test_improved_scheduled_creates_pending_scheduled_review(): void
    {
        $scheduledDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'IMPROVED_SCHEDULED',
                'next_visit_date' => $scheduledDate,
                'follow_up_objective' => 'موعد متابعة',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // Registration remains active while follow-up is scheduled
        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        // Scheduled review should be created and PENDING
        $this->assertDatabaseHas('wc_scheduled_review_tb', [
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        // Daily review header should link to the scheduled review
        $this->assertDatabaseHas('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_SCHEDULED',
        ]);
    }

    /**
     * US4: Discharged registration blocks new daily review creation (HTTP 403).
     */
    public function test_discharged_registration_blocks_new_daily_review(): void
    {
        $this->registration->update([
            'WC_REG_STATUS' => 'DISCHARGED',
            'WC_REG_DISCHARGED_AT' => now(),
            'WC_REG_DISCHARGED_BY_EMP_CD' => $this->nurseEmployee->PER_EMP_CODE,
        ]);

        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // No daily review record should have been created
        $this->assertDatabaseMissing('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
        ]);
    }

    public function test_discharged_registration_cannot_open_daily_review_entry_form(): void
    {
        $this->registration->update([
            'WC_REG_STATUS' => 'DISCHARGED',
            'WC_REG_DISCHARGED_AT' => now(),
            'WC_REG_DISCHARGED_BY_EMP_CD' => $this->nurseEmployee->PER_EMP_CODE,
        ]);

        $response = $this->call(
            'GET',
            "/reviews/daily/{$this->registration->WC_REG_CODE}/create",
            [],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect(route('reviews.daily.pending'));
        $response->assertSessionHas('error', 'لا يمكن إضافة مراجعة يومية لمريض مخرج');
    }

    /**
     * Per-wound IMPROVED_DISCHARGE does NOT by itself discharge the registration
     * when the overall visit outcome is WORSENED_STAY.
     */
    public function test_per_wound_improved_discharge_does_not_discharge_registration(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'IMPROVED_DISCHARGE',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // Registration stays ACTIVE because overall outcome is WORSENED_STAY
        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        // But the per-wound outcome is correctly stored as IMPROVED_DISCHARGE
        $this->assertDatabaseHas('wc_review_detail_tb', [
            'WC_DREVD_REG_DIAGNOSIS_CD' => $this->regDiagnosis->WC_RDIAG_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_DISCHARGE',
        ]);
    }

    /**
     * Overall WORSENED_STAY does not discharge the registration.
     */
    public function test_overall_worsened_stay_does_not_discharge_registration(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'WORSENED',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $this->assertDatabaseMissing('wc_scheduled_review_tb', [
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
        ]);
    }

    /**
     * Overall DEATH follows header workflow without triggering discharge or scheduled review.
     */
    public function test_overall_death_follows_header_workflow_without_wound_confusion(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'DEATH',
                'general_notes' => 'تسجيل حالة وفاة',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'outcome_status' => 'DECEASED',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // DEATH closes the registration.
        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_STATUS' => 'DISCHARGED',
        ]);

        // No scheduled review created
        $this->assertDatabaseMissing('wc_scheduled_review_tb', [
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
        ]);

        // Header record correctly stores DEATH
        $this->assertDatabaseHas('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_OVERALL_STATUS' => 'DEATH',
        ]);
    }

    /**
     * Dressing and medication are stored in daily_review_detail_tb, not in the header or diagnosis tables.
     */
    public function test_dressing_and_medication_are_stored_in_daily_review_detail_tb(): void
    {
        $response = $this->call(
            'POST',
            "/registrations/{$this->registration->WC_REG_CODE}/daily-reviews",
            [
                'submission_key' => (string) Str::uuid(),
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'details' => [
                    [
                        ...$this->structuredDetail(),
                        'registration_diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'dressing_code' => $this->dressing->WC_DRESS_CODE,
                        'medication_code' => $this->medication->WC_MED_CODE,
                        'observation' => 'فحص ضمادة وعلاج',
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // Dressing and medication stored in detail table
        $this->assertDatabaseHas('wc_review_detail_tb', [
            'WC_DREVD_REG_DIAGNOSIS_CD' => $this->regDiagnosis->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $this->dressing->WC_DRESS_CODE,
            'WC_DREVD_MEDICATION_CD' => $this->medication->WC_MED_CODE,
        ]);

        // Dressing/medication columns do NOT exist on wc_reg_diagnosis_tb
        $this->assertDatabaseMissing('wc_reg_diagnosis_tb', [
            'WC_RDIAG_CODE' => $this->regDiagnosis->WC_RDIAG_CODE,
            'WC_RDIAG_DRESSING_CD' => $this->dressing->WC_DRESS_CODE,
        ]);
    }

    /** @return array<string, mixed> */
    private function structuredDetail(): array
    {
        return [
            'wound_status_choice_code' => $this->choice('WOUND_STATUS', 'IMPROVING'),
            'wound_bed_choice_codes' => [$this->choice('WOUND_BED', 'GRANULATION')],
            'wound_margin_choice_codes' => [],
            'exudate_amount_choice_code' => $this->choice('EXUDATE_AMOUNT', 'DRY'),
            'exudate_type_choice_code' => null,
            'odor_choice_code' => $this->choice('ODOR', 'NONE'),
            'pain_severity_choice_code' => $this->choice('PAIN_SEVERITY', 'NONE'),
            'pain_at_dressing' => false,
            'treatment_notes' => 'خطة علاج اختبارية',
            'follow_up_required' => true,
        ];
    }

    private function choice(string $category, string $key): string
    {
        return WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_KEY', $key)
            ->valueOrFail('WC_ACH_CODE');
    }
}
