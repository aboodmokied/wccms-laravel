<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\PerUser;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundPhoto;
use App\Models\WcWoundType;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WoundPhotoDocumentationTest extends TestCase
{
    use RefreshDatabase;

    protected PerUser $user;

    protected Employee $employee;

    protected WcDepartment $department;

    protected WcWoundType $woundType;

    protected WcDiagnosis $diagnosis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WcAssessmentChoiceSeeder::class);

        $this->user = PerUser::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'test_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = Employee::factory()->named('Nurse Sarah')->create();

        $this->user->update(['PER_USER_EMP_CD' => $this->employee->EMP_SERIAL]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'Wound Care Clinic',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $this->woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'Diabetic Foot Ulcer',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        $this->diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'Grade 2 Ulcer',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);
    }

    public function test_initial_wound_photo_is_stored_during_registration_acceptance(): void
    {
        Storage::fake('public');

        $patient = MrPatient::factory()->male()->named('John Doe')->create([
            'MRP_ID' => 123456789,
        ]);

        $requestModel = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $photoFile = UploadedFile::fake()->image('wound_baseline.jpg', 800, 600);

        $response = $this->post(route('requests.accept', $requestModel->WC_REQ_CODE), [
            'clinic_nurse_code' => $this->employee->EMP_SERIAL,
            'department_code' => $this->department->WC_DEPT_CODE,
            'has_allergies' => false,
            'no_comorbidities' => true,
            'next_visit_date' => now()->addWeek()->toDateString(),
            'follow_up_objective' => 'متابعة الجرح',
            'diagnoses' => [
                [
                    ...$this->structuredAssessment(true),
                    'wound_type_code' => $this->woundType->WC_WTYPE_CODE,
                    'diagnosis_code' => $this->diagnosis->WC_DIAG_CODE,
                    'wound_photos' => [$photoFile],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_wound_photo_tb', [
            'WC_PHOTO_STAGE' => 'INITIAL',
            'WC_PHOTO_NURSE_CD' => $this->employee->EMP_SERIAL,
        ]);

        $photo = WcWoundPhoto::first();
        $this->assertNotNull($photo);
        Storage::disk('public')->assertExists($photo->WC_PHOTO_PATH);
    }

    public function test_follow_up_wound_photo_is_stored_during_daily_review(): void
    {
        Storage::fake('public');

        $patient = MrPatient::factory()->female()->named('Jane Smith')->create([
            'MRP_ID' => 987654321,
        ]);

        $requestModel = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $requestModel->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $regDiag = WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $this->woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $this->diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->employee->EMP_SERIAL,
            'WC_RDIAG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
        ]);

        $reviewPhotoFile = UploadedFile::fake()->image('review_followup.png', 800, 600);

        $response = $this->post(route('registrations.daily-reviews.store', $registration->WC_REG_CODE), [
            'submission_key' => (string) Str::uuid(),
            'nurse_code' => $this->employee->EMP_SERIAL,
            'overall_status' => 'WORSENED_STAY',
            'general_notes' => 'متابعة سريرية للجرح',
            'details' => [
                [
                    ...$this->structuredAssessment(false),
                    'registration_diagnosis_code' => $regDiag->WC_RDIAG_CODE,
                    'outcome_status' => 'IMPROVED_FOLLOWUP',
                    'clinical_observation' => 'تحسن في أطراف الجرح',
                    'wound_photos' => [$reviewPhotoFile],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_wound_photo_tb', [
            'WC_PHOTO_REG_DIAGNOSIS_CD' => $regDiag->WC_RDIAG_CODE,
            'WC_PHOTO_STAGE' => 'REVIEW',
        ]);

        $photo = WcWoundPhoto::where('WC_PHOTO_STAGE', 'REVIEW')->first();
        $this->assertNotNull($photo);
        Storage::disk('public')->assertExists($photo->WC_PHOTO_PATH);

        $detailsResponse = $this->get(route('registrations.show', $registration->WC_REG_CODE));

        $detailsResponse->assertOk();
        $detailsResponse->assertSee('صور الجرح');
        $detailsResponse->assertSee(route('wound_photos.show', $photo->WC_PHOTO_CODE), false);
        $detailsResponse->assertDontSee('id="wound-photos"', false);
    }

    /** @return array<string, mixed> */
    private function structuredAssessment(bool $initial): array
    {
        $choice = fn (string $category, string $key): string => WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)->where('WC_ACH_KEY', $key)->valueOrFail('WC_ACH_CODE');
        $payload = [
            'wound_status_choice_code' => $choice('WOUND_STATUS', $initial ? 'FIRST_VISIT' : 'IMPROVING'),
            'wound_bed_choice_codes' => [$choice('WOUND_BED', 'GRANULATION')],
            'wound_margin_choice_codes' => $initial ? [$choice('WOUND_MARGIN', 'NONE_OBSERVED')] : [],
            'exudate_amount_choice_code' => $choice('EXUDATE_AMOUNT', 'DRY'),
            'odor_choice_code' => $choice('ODOR', 'NONE'),
            'pain_severity_choice_code' => $choice('PAIN_SEVERITY', 'NONE'),
            'pain_at_dressing' => false,
            'treatment_notes' => 'تنظيف الجرح',
            'follow_up_required' => true,
        ];
        if ($initial) {
            $payload += [
                'fever_choice_code' => $choice('FEVER', 'NONE'), 'length_mm' => 10, 'width_mm' => 8, 'depth_mm' => 0,
                'location_choice_code' => $choice('LOCATION', 'FOOT'), 'laterality_choice_code' => $choice('LATERALITY', 'LEFT'),
                'swab_taken' => false, 'nutrition_choice_code' => $choice('NUTRITION', 'WELL_NOURISHED'),
            ];
        }

        return $payload;
    }
}
