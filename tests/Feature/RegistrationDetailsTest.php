<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcDailyReview;
use App\Models\WcDepartment;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $nurseUser;

    private Employee $nurseEmployee;

    private WcDepartment $department;

    private WcProcedure $procedure;

    private WcRegistration $registration;

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

        $this->nurseEmployee = Employee::factory()->named('ممرض فحص تجريبي')->create();

        $this->nurseUser->update(['PER_USER_EMP_CD' => $this->nurseEmployee->EMP_SERIAL]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم الجراحة',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $this->procedure = WcProcedure::create([
            'WC_PROC_CODE' => (string) Str::uuid(),
            'WC_PROC_NAME' => 'تنظيف وتنضير الجرح (Debridement)',
            'WC_PROC_IS_ACTIVE' => 1,
        ]);

        $patient = MrPatient::factory()->male()->named('أحمد محمود علي')->create([
            'MRP_ID' => 100200300,
        ]);

        $request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $this->registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');
        $this->jwtToken = JWT::encode([
            'sub' => $this->nurseUser->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $secret, 'HS256');
    }

    public function test_can_view_registration_details_page(): void
    {
        $response = $this->call('GET', "/registrations/{$this->registration->WC_REG_CODE}", [], ['hospital_token' => $this->jwtToken]);

        $response->assertStatus(200);
        $response->assertSee('أحمد محمود علي');
        $response->assertSee('الملف السريري للمريض');
        $response->assertSee('سجل المراجعات');
    }

    public function test_reviews_are_displayed_from_newest_to_oldest(): void
    {
        WcDailyReview::create([
            'WC_DREV_CODE' => (string) Str::uuid(),
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_VISIT_DATE' => now()->subDays(2),
            'WC_DREV_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_DREV_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY',
            'WC_DREV_GENERAL_NOTES' => 'المراجعة الأقدم',
        ]);

        WcDailyReview::create([
            'WC_DREV_CODE' => (string) Str::uuid(),
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_VISIT_DATE' => now()->subDay(),
            'WC_DREV_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_DREV_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_STAY',
            'WC_DREV_GENERAL_NOTES' => 'المراجعة الأحدث',
        ]);

        $response = $this->call('GET', "/registrations/{$this->registration->WC_REG_CODE}", [], ['hospital_token' => $this->jwtToken]);

        $response->assertOk();
        $response->assertSeeInOrder(['المراجعة الأحدث', 'المراجعة الأقدم']);
    }

    public function test_can_update_registration_procedure_and_pmh_notes(): void
    {
        $response = $this->call('PUT', "/registrations/{$this->registration->WC_REG_CODE}", [
            'procedure_code' => $this->procedure->WC_PROC_CODE,
            'pmh_notes' => 'تاريخ مرضي سابق: السكري من النوع الثاني',
        ], ['hospital_token' => $this->jwtToken]);

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_registration_tb', [
            'WC_REG_CODE' => $this->registration->WC_REG_CODE,
            'WC_REG_PROCEDURE_CD' => $this->procedure->WC_PROC_CODE,
            'WC_REG_PMH_NOTES' => 'تاريخ مرضي سابق: السكري من النوع الثاني',
        ]);
    }
}
