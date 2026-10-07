<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Models\WcWoundType;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduledReviewTest extends TestCase
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

        $this->nurseUser = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'test_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->nurseEmployee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'ممرض المواعيد',
            'PER_EMP_USER_CD' => $this->nurseUser->PER_USER_CODE,
        ]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم العناية بالجروح',
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
            'MR_PAT_ID_NO' => '1099887766',
            'MR_PAT_NATIONAL_ID' => '1099887766',
            'MR_PAT_FULL_NAME' => 'عبدالله خالد العتيبي',
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
            'WC_WTYPE_NAME' => 'قدم سكرية',
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

    /**
     * US1 (T006): View scheduled review queue dashboard and verify status summary counts.
     */
    public function test_can_view_scheduled_reviews_queue_and_counts(): void
    {
        // Create today review
        WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->startOfDay()->addHours(10),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        // Create upcoming review
        WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(3),
            'WC_SREV_VISIT_SERIAL' => 2,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        // Create overdue review
        WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->subDays(2),
            'WC_SREV_VISIT_SERIAL' => 3,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'GET',
            '/reviews/scheduled',
            ['tab' => 'TODAY'],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertOk();
        $response->assertViewIs('reviews.scheduled');
        $response->assertSee('عبدالله خالد العتيبي');
        $response->assertSee('تابع مواعيد اليوم وابدأ الزيارة');
    }

    /**
     * US1 (T006): Dynamic tab filtering and search by patient name.
     */
    public function test_can_filter_scheduled_reviews_by_tab_and_search(): void
    {
        $upcoming = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(5),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'GET',
            '/reviews/scheduled',
            ['tab' => 'UPCOMING', 'search' => 'عبدالله'],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertOk();
        $response->assertSee('عبدالله خالد العتيبي');
        $response->assertSee(Str::limit($upcoming->WC_SREV_CODE, 8, ''));
    }

    public function test_scheduled_review_entry_form_is_separate_from_patient_history(): void
    {
        $scheduledReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now(),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'GET',
            "/reviews/scheduled/{$scheduledReview->WC_SREV_CODE}/create",
            [],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertOk();
        $response->assertViewIs('reviews.daily_create');
        $response->assertSee('توثيق مراجعة مجدولة');
        $response->assertSee('عرض التاريخ الصحي');
    }

    public function test_legacy_start_fulfillment_redirects_to_scheduled_review_entry_form(): void
    {
        $scheduledReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now(),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'POST',
            "/reviews/scheduled/{$scheduledReview->WC_SREV_CODE}/fulfill",
            [],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect(route('reviews.scheduled.create', $scheduledReview));
    }

    /**
     * US2 (T010): Saving daily review auto-completes the linked scheduled review appointment.
     */
    public function test_saving_daily_review_auto_completes_scheduled_review(): void
    {
        $scheduledReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now(),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'POST',
            "/reviews/scheduled/{$scheduledReview->WC_SREV_CODE}",
            [
                'nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'general_notes' => 'إنجاز الموعد المجدول',
                'details' => [
                    [
                        'diagnosis_code' => $this->regDiagnosis->WC_RDIAG_CODE,
                        'dressing_code' => $this->dressing->WC_DRESS_CODE,
                        'medication_code' => $this->medication->WC_MED_CODE,
                        'observation' => 'فحص الزيارة المجدولة',
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect(route('reviews.scheduled'));

        // Verify scheduled review is updated to COMPLETED
        $this->assertDatabaseHas('wc_scheduled_review_tb', [
            'WC_SREV_CODE' => $scheduledReview->WC_SREV_CODE,
            'WC_SREV_STATUS' => 'COMPLETED',
        ]);

        // Verify daily review is linked
        $this->assertDatabaseHas('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduledReview->WC_SREV_CODE,
        ]);
    }

    /**
     * US3 (T014): Reschedule pending appointment date.
     */
    public function test_can_reschedule_pending_appointment(): void
    {
        $scheduledReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(2),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $newDate = now()->addDays(7)->format('Y-m-d H:i:s');

        $response = $this->call(
            'PUT',
            "/reviews/scheduled/{$scheduledReview->WC_SREV_CODE}/reschedule",
            [
                'scheduled_date' => $newDate,
                'notes' => 'تأجيل الموعد بطلب المريض',
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wc_scheduled_review_tb', [
            'WC_SREV_CODE' => $scheduledReview->WC_SREV_CODE,
            'WC_SREV_SCHEDULED_DATE' => $newDate,
            'WC_SREV_STATUS' => 'PENDING',
        ]);
    }

    /**
     * US3 (T014): Manual cancellation of pending appointment.
     */
    public function test_can_cancel_pending_appointment(): void
    {
        $scheduledReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(2),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $response = $this->call(
            'POST',
            "/reviews/scheduled/{$scheduledReview->WC_SREV_CODE}/cancel",
            [
                'notes' => 'تعذر حضور المريض',
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wc_scheduled_review_tb', [
            'WC_SREV_CODE' => $scheduledReview->WC_SREV_CODE,
            'WC_SREV_STATUS' => 'CANCELLED',
        ]);
    }

    /**
     * US4 (T018): Hard deletion of old pending appointments on re-registration.
     */
    public function test_re_registration_hard_deletes_old_pending_scheduled_reviews(): void
    {
        // Patient's first registration has a PENDING scheduled review
        $oldPendingReview = WcScheduledReview::create([
            'WC_SREV_CODE' => (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(5),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        // Create a new registration request for the SAME patient
        $patientCode = $this->registration->WC_REG_PATIENT_CD;
        $patient = MrPatient::find($patientCode);

        $newRequest = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patientCode,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->nurseEmployee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        // Accept the new request to form a new registration
        $response = $this->call(
            'POST',
            "/requests/{$newRequest->WC_REQ_CODE}/accept",
            [
                'clinic_nurse_code' => $this->nurseEmployee->PER_EMP_CODE,
                'department_code' => $this->department->WC_DEPT_CODE,
                'diagnoses' => [
                    [
                        'wound_type_code' => $this->regDiagnosis->WC_RDIAG_WOUND_TYPE_CD,
                        'diagnosis_code' => $this->regDiagnosis->WC_RDIAG_DIAGNOSIS_CD,
                    ],
                ],
            ],
            ['hospital_token' => $this->jwtToken]
        );

        $response->assertRedirect();

        // Old PENDING scheduled review must be hard-deleted permanently from database
        $this->assertDatabaseMissing('wc_scheduled_review_tb', [
            'WC_SREV_CODE' => $oldPendingReview->WC_SREV_CODE,
        ]);
    }
}
