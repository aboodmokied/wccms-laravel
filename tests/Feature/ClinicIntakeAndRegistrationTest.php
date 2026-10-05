<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClinicIntakeAndRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PerEmployee $employee;

    private MrPatient $patient;

    private WcDepartment $department;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hospital_auth.jwt_secret' => $this->secret]);

        $this->user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'clinic_nurse_1',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'ممرض عيادة الجروح',
            'PER_EMP_JOB_TITLE' => 'ممرض متخصص',
            'PER_EMP_USER_CD' => $this->user->PER_USER_CODE,
        ]);

        $this->patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '401234567',
            'MR_PAT_FULL_NAME' => 'محمود مصطفى طه',
            'MR_PAT_PHONE' => '0599223344',
            'MR_PAT_GENDER' => 'MALE',
            'MR_PAT_DOB' => '1990-01-01',
            'MR_PAT_LAST_SYNCED_AT' => now(),
        ]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'عيادة العناية بالجروح والتخاريج',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);
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

    public function test_clinic_nurse_can_view_incoming_queue(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get(route('requests.index'));

        $response->assertStatus(200);
        $response->assertSee('محمود مصطفى طه');
    }

    public function test_clinic_nurse_can_start_ward_visit(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.visit', $req->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.show', $req->WC_REQ_CODE));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->PER_EMP_CODE,
        ]);
    }

    public function test_clinic_nurse_can_accept_referral_and_create_registration(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('registrations.index'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
        ]);

        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_REQUEST_CD' => $req->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);
    }

    public function test_clinic_nurse_can_accept_referral_with_clinical_data_and_diagnosis(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $procedure = WcProcedure::create([
            'WC_PROC_CODE' => (string) Str::uuid(),
            'WC_PROC_NAME' => 'تنظيف وتضميد جراح أول',
            'WC_PROC_IS_ACTIVE' => 1,
        ]);

        $woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'جرح قرحة فراش',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        $diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'قرحة درجة ثانية',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
                'department_code' => $this->department->WC_DEPT_CODE,
                'procedure_code' => $procedure->WC_PROC_CODE,
                'pmh_notes' => 'المريض يعاني من السكري الضغط',
                'wound_type_code' => $woundType->WC_WTYPE_CODE,
                'diagnosis_code' => $diagnosis->WC_DIAG_CODE,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('registrations.index'));

        $registration = WcRegistration::where('WC_REG_REQUEST_CD', $req->WC_REQ_CODE)->first();
        $this->assertNotNull($registration);
        $this->assertEquals($procedure->WC_PROC_CODE, $registration->WC_REG_PROCEDURE_CD);
        $this->assertEquals('المريض يعاني من السكري الضغط', $registration->WC_REG_PMH_NOTES);

        $this->assertDatabaseHas('wc_reg_diagnosis_tb', [
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->employee->PER_EMP_CODE,
        ]);
    }

    public function test_clinic_nurse_can_mark_referral_ineligible(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.ineligible', $req->WC_REQ_CODE), [
                'reason' => 'الحالة السريرية للمريض لا تتطلب متابعة العيادة المتخصصة',
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.index'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'NOT_ELIGIBLE',
        ]);
    }

    public function test_clinic_nurse_can_accept_referral_with_initial_medication_creates_daily_review(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'جرح سكري',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        $diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'قدم سكرية',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        $medication = WcMedication::create([
            'WC_MED_CODE' => (string) Str::uuid(),
            'WC_MED_NAME' => 'مضاد حيوي عريض الطيف',
            'WC_MED_IS_ACTIVE' => 1,
        ]);

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
                'department_code' => $this->department->WC_DEPT_CODE,
                'wound_type_code' => $woundType->WC_WTYPE_CODE,
                'diagnosis_code' => $diagnosis->WC_DIAG_CODE,
                'medication_code' => $medication->WC_MED_CODE,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('registrations.index'));

        $registration = WcRegistration::where('WC_REG_REQUEST_CD', $req->WC_REQ_CODE)->first();
        $this->assertNotNull($registration);

        $this->assertDatabaseHas('wc_daily_review_tb', [
            'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY',
        ]);
    }
}
