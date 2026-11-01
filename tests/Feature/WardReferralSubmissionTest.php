<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcRequestReg;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WardReferralSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PerEmployee $employee;

    private MrPatient $patient;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hospital_auth.jwt_secret' => $this->secret]);

        $this->user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'ward_nurse_1',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'ممرض قسم الجراحة',
            'PER_EMP_JOB_TITLE' => 'ممرض قانوني',
        ]);

        $this->user->update(['PER_USER_EMP_CD' => $this->employee->PER_EMP_CODE]);

        $doctor = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'د. سامي الخطيب',
            'PER_EMP_JOB_TITLE' => 'طبيب استشاري',
        ]);

        $this->patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '401234567',
            'MR_PAT_SUPERVISING_DOCTOR_CD' => $doctor->PER_EMP_CODE,
            'MR_PAT_FULL_NAME' => 'أحمد علي حسن',
            'MR_PAT_PHONE' => '0599123456',
            'MR_PAT_GENDER' => 'MALE',
            'MR_PAT_DOB' => '1985-04-12',
            'MR_PAT_LAST_SYNCED_AT' => now(),
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

    public function test_ward_nurse_can_search_patient_and_receive_admission_details(): void
    {
        $token = $this->generateJwt('ward_nurse_1');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('requests.search_patient', ['query' => '401234567']));

        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'patient' => [
                'code' => $this->patient->MR_PAT_CODE,
                'id_number' => '401234567',
                'full_name' => 'أحمد علي حسن',
            ],
            'admission' => ['attending_doctor' => 'د. سامي الخطيب'],
            'has_pending_request' => false,
            'has_active_registration' => false,
        ]);
    }

    public function test_searching_unknown_id_number_returns_not_found_without_creating_patient(): void
    {
        $token = $this->generateJwt('ward_nurse_1');
        $patientCountBefore = MrPatient::count();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('requests.search_patient', ['query' => '000000000']));

        $response->assertNotFound();
        $response->assertJson(['found' => false]);
        $this->assertSame($patientCountBefore, MrPatient::count());
        $this->assertDatabaseMissing(MrPatient::class, ['MR_PAT_ID_NO' => '000000000']);
    }

    public function test_ward_nurse_can_submit_referral_request_with_snapshot_data(): void
    {
        $token = $this->generateJwt('ward_nurse_1');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.store'), [
                'patient_code' => $this->patient->MR_PAT_CODE,
                'attending_nurse_code' => $this->employee->PER_EMP_CODE,
                'notes' => 'جرح ما بعد العملية الجراحية يحتاج تقييم العيادة',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.my'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'أحمد علي حسن',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_NOTES' => 'جرح ما بعد العملية الجراحية يحتاج تقييم العيادة',
        ]);
    }

    public function test_duplicate_referral_submission_is_prevented(): void
    {
        $token = $this->generateJwt('ward_nurse_1');

        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'أحمد علي حسن',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.store'), [
                'patient_code' => $this->patient->MR_PAT_CODE,
                'attending_nurse_code' => $this->employee->PER_EMP_CODE,
                'notes' => 'طلب مكرر',
            ]);

        $response->assertSessionHasErrors(['patient_code']);
    }
}
