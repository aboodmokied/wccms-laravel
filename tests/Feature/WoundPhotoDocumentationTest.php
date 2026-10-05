<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\PerUser;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundPhoto;
use App\Models\WcWoundType;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WoundPhotoDocumentationTest extends TestCase
{
    use RefreshDatabase;

    protected PerUser $user;

    protected PerEmployee $employee;

    protected WcDepartment $department;

    protected WcWoundType $woundType;

    protected WcDiagnosis $diagnosis;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hospital_auth.jwt_secret' => $this->secret]);

        $this->user = PerUser::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'test_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'Nurse Sarah',
            'PER_EMP_JOB_TITLE' => 'Nurse Specialist',
            'PER_EMP_USER_CD' => $this->user->PER_USER_CODE,
            'PER_EMP_IS_ACTIVE' => 1,
        ]);

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

        $this->actingAs($this->user);
    }

    private function generateJwt(string $username): string
    {
        $payload = [
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'hospital-auth-system',
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function test_initial_wound_photo_is_stored_during_registration_acceptance(): void
    {
        Storage::fake('public');
        $token = $this->generateJwt('test_nurse');

        $patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '123456789',
            'MR_PAT_FULL_NAME' => 'John Doe',
            'MR_PAT_GENDER' => 'MALE',
        ]);

        $requestModel = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $photoFile = UploadedFile::fake()->image('wound_baseline.jpg', 800, 600);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $requestModel->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
                'department_code' => $this->department->WC_DEPT_CODE,
                'diagnoses' => [
                    [
                        'wound_type_code' => $this->woundType->WC_WTYPE_CODE,
                        'diagnosis_code' => $this->diagnosis->WC_DIAG_CODE,
                        'wound_photos' => [$photoFile],
                    ],
                ],
            ]);

        $response->assertRedirect(route('registrations.index'));

        $this->assertDatabaseHas('wc_wound_photo_tb', [
            'WC_PHOTO_STAGE' => 'INITIAL',
            'WC_PHOTO_NURSE_CD' => $this->employee->PER_EMP_CODE,
        ]);

        $photo = WcWoundPhoto::first();
        $this->assertNotNull($photo);
        Storage::disk('public')->assertExists($photo->WC_PHOTO_PATH);
    }

    public function test_follow_up_wound_photo_is_stored_during_daily_review(): void
    {
        Storage::fake('public');
        $token = $this->generateJwt('test_nurse');

        $patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '987654321',
            'MR_PAT_FULL_NAME' => 'Jane Smith',
            'MR_PAT_GENDER' => 'FEMALE',
        ]);

        $requestModel = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $requestModel->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $regDiag = WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $this->woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $this->diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->employee->PER_EMP_CODE,
            'WC_RDIAG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
        ]);

        $reviewPhotoFile = UploadedFile::fake()->image('review_followup.png', 800, 600);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('registrations.daily-reviews.store', $registration->WC_REG_CODE), [
                'nurse_code' => $this->employee->PER_EMP_CODE,
                'overall_status' => 'WORSENED_STAY',
                'general_notes' => 'متابعة سريرية للجرح',
                'details' => [
                    [
                        'diagnosis_code' => $regDiag->WC_RDIAG_CODE,
                        'outcome_status' => 'IMPROVED_FOLLOWUP',
                        'observation' => 'تحسن في أطراف الجرح',
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
    }
}
