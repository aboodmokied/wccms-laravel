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

class MyRequestsAndCancellationTest extends TestCase
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
            'PER_USER_USERNAME' => 'ward_nurse_owner',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'ممرض القسم صاحب الطلب',
            'PER_EMP_JOB_TITLE' => 'ممرض قانوني',
            'PER_EMP_USER_CD' => $this->user->PER_USER_CODE,
        ]);

        $this->patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '401234567',
            'MR_PAT_FULL_NAME' => 'خالد عبد الله سعيد',
            'MR_PAT_PHONE' => '0599334455',
            'MR_PAT_GENDER' => 'MALE',
            'MR_PAT_DOB' => '1975-11-20',
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

    public function test_ward_nurse_can_view_submitted_referrals_list(): void
    {
        $token = $this->generateJwt('ward_nurse_owner');

        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get(route('requests.my'));

        $response->assertStatus(200);
        $response->assertSee('خالد عبد الله سعيد');
    }

    public function test_ward_nurse_can_cancel_pending_referral(): void
    {
        $token = $this->generateJwt('ward_nurse_owner');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.cancel', $req->WC_REQ_CODE));

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.my'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'CANCELLED',
        ]);
    }

    public function test_ward_nurse_cannot_cancel_handled_referral(): void
    {
        $token = $this->generateJwt('ward_nurse_owner');

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post(route('requests.cancel', $req->WC_REQ_CODE));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
        ]);
    }
}
