<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcDepartment;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationListingTest extends TestCase
{
    use RefreshDatabase;

    private User $nurseUser;

    private Employee $nurseEmployee;

    private WcDepartment $department;

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

        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');
        $this->jwtToken = JWT::encode([
            'sub' => $this->nurseUser->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $secret, 'HS256');
    }

    public function test_can_list_active_registrations_by_default(): void
    {
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

        $registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $response = $this->call('GET', '/registrations', [], ['hospital_token' => $this->jwtToken]);

        $response->assertStatus(200);
        $response->assertSee('أحمد محمود علي');
        $response->assertSee('100200300');
    }

    public function test_can_filter_discharged_registrations(): void
    {
        $patient = MrPatient::factory()->female()->named('فاطمة خليل')->create([
            'MRP_ID' => 999888777,
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

        WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'DISCHARGED',
            'WC_REG_DISCHARGED_AT' => now(),
            'WC_REG_DISCHARGED_BY_EMP_CD' => $this->nurseEmployee->EMP_SERIAL,
        ]);

        $response = $this->call('GET', '/registrations', ['status' => 'DISCHARGED'], ['hospital_token' => $this->jwtToken]);

        $response->assertStatus(200);
        $response->assertSee('فاطمة خليل');
    }
}
