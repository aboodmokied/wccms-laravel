<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClinicIntakeAndRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Employee $employee;

    private MrPatient $patient;

    private WcDepartment $department;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WcAssessmentChoiceSeeder::class);
        config(['services.hospital_auth.jwt_secret' => $this->secret]);

        $this->user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'clinic_nurse_1',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = Employee::factory()->named('ممرض عيادة الجروح')->create();

        $this->user->update(['PER_USER_EMP_CD' => $this->employee->EMP_SERIAL]);

        $this->patient = MrPatient::factory()->male()->named('محمود مصطفى طه')->create([
            'MRP_ID' => 401234567,
            'MRP_MOBILE_NO' => '0599223344',
            'MRP_DOB' => '1990-01-01',
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
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
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
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.visit', $req->WC_REQ_CODE), [
                'clinic_nurse_code' => $this->employee->EMP_SERIAL,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.show', $req->WC_REQ_CODE));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->EMP_SERIAL,
        ]);
    }

    public function test_clinic_nurse_can_accept_referral_and_create_registration(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), $this->structuredAcceptancePayload());

        $response->assertStatus(302);

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
        ]);

        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_REQUEST_CD' => $req->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);
        $registration = WcRegistration::where('WC_REG_REQUEST_CD', $req->WC_REQ_CODE)->firstOrFail();
        $response->assertRedirect(route('registrations.show', $registration));
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
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), $this->structuredAcceptancePayload($woundType, $diagnosis, [
                'procedure_code' => $procedure->WC_PROC_CODE,
                'pmh_notes' => 'المريض يعاني من السكري الضغط',
            ]));

        $response->assertStatus(302);

        $registration = WcRegistration::where('WC_REG_REQUEST_CD', $req->WC_REQ_CODE)->first();
        $this->assertNotNull($registration);
        $response->assertRedirect(route('registrations.show', $registration));
        $this->assertEquals($procedure->WC_PROC_CODE, $registration->WC_REG_PROCEDURE_CD);
        $this->assertEquals('المريض يعاني من السكري الضغط', $registration->WC_REG_PMH_NOTES);

        $this->assertDatabaseHas('wc_reg_diagnosis_tb', [
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->employee->EMP_SERIAL,
        ]);
    }

    public function test_clinic_nurse_can_mark_referral_ineligible(): void
    {
        $token = $this->generateJwt('clinic_nurse_1');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.ineligible', $req->WC_REQ_CODE), [
                'reason' => 'الحالة السريرية للمريض لا تتطلب متابعة العيادة المتخصصة',
                'clinic_nurse_code' => $this->employee->EMP_SERIAL,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.index'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'NOT_ELIGIBLE',
        ]);
    }

    public function test_clinic_nurse_can_accept_referral_with_initial_medication_creates_baseline_assessment(): void
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
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'محمود مصطفى طه',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.accept', $req->WC_REQ_CODE), $this->structuredAcceptancePayload($woundType, $diagnosis, [
                'medication_code' => $medication->WC_MED_CODE,
            ]));

        $response->assertStatus(302);

        $registration = WcRegistration::where('WC_REG_REQUEST_CD', $req->WC_REQ_CODE)->first();
        $this->assertNotNull($registration);
        $response->assertRedirect(route('registrations.show', $registration));

        $this->assertDatabaseHas('wc_wound_assessment_tb', [
            'WC_WA_TYPE' => 'INITIAL',
            'WC_WA_MEDICATION_CD' => $medication->WC_MED_CODE,
        ]);
    }

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function structuredAcceptancePayload(
        ?WcWoundType $woundType = null,
        ?WcDiagnosis $diagnosis = null,
        array $overrides = [],
    ): array {
        $woundType ??= WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(), 'WC_WTYPE_NAME' => 'جرح اختباري', 'WC_WTYPE_IS_ACTIVE' => 1,
        ]);
        $diagnosis ??= WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(), 'WC_DIAG_NAME' => 'تشخيص اختباري', 'WC_DIAG_IS_ACTIVE' => 1,
        ]);
        $choice = fn (string $category, string $key): string => WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)->where('WC_ACH_KEY', $key)->valueOrFail('WC_ACH_CODE');
        $shared = array_intersect_key($overrides, array_flip(['procedure_code', 'pmh_notes']));
        $treatment = array_intersect_key($overrides, array_flip(['medication_code', 'dressing_code', 'procedure_code', 'treatment_notes']));

        return [
            ...$shared,
            'department_code' => $this->department->WC_DEPT_CODE,
            'clinic_nurse_code' => $this->employee->EMP_SERIAL,
            'has_allergies' => false,
            'no_comorbidities' => true,
            'next_visit_date' => now()->addWeek()->toDateString(),
            'follow_up_objective' => 'متابعة التئام الجرح',
            'diagnoses' => [[
                'wound_type_code' => $woundType->WC_WTYPE_CODE,
                'diagnosis_code' => $diagnosis->WC_DIAG_CODE,
                'wound_status_choice_code' => $choice('WOUND_STATUS', 'FIRST_VISIT'),
                'wound_bed_choice_codes' => [$choice('WOUND_BED', 'GRANULATION')],
                'wound_margin_choice_codes' => [$choice('WOUND_MARGIN', 'NONE_OBSERVED')],
                'exudate_amount_choice_code' => $choice('EXUDATE_AMOUNT', 'DRY'),
                'odor_choice_code' => $choice('ODOR', 'NONE'),
                'pain_severity_choice_code' => $choice('PAIN_SEVERITY', 'NONE'),
                'pain_at_dressing' => false,
                'fever_choice_code' => $choice('FEVER', 'NONE'),
                'length_mm' => 12.5,
                'width_mm' => 8,
                'depth_mm' => 0,
                'location_choice_code' => $choice('LOCATION', 'FOOT'),
                'laterality_choice_code' => $choice('LATERALITY', 'LEFT'),
                'swab_taken' => false,
                'nutrition_choice_code' => $choice('NUTRITION', 'WELL_NOURISHED'),
                'treatment_notes' => 'تنظيف ومتابعة',
                ...$treatment,
            ]],
        ];
    }
}
