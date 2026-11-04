<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcRequestReg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MyRequestsAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Employee $employee;

    private MrPatient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'ward_nurse_owner',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = Employee::factory()->named('ممرض القسم صاحب الطلب')->create();

        $this->user->update(['PER_USER_EMP_CD' => $this->employee->EMP_SERIAL]);

        $this->patient = MrPatient::factory()->male()->named('خالد عبد الله سعيد')->create([
            'MRP_ID' => 401234567,
            'MRP_MOBILE_NO' => '0599334455',
            'MRP_DOB' => '1975-11-20',
        ]);
    }

    public function test_ward_nurse_can_view_submitted_referrals_list(): void
    {
        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->get(route('requests.my'));

        $response->assertStatus(200);
        $response->assertSee('خالد عبد الله سعيد');
    }

    public function test_ward_nurse_can_cancel_pending_referral(): void
    {
        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->post(route('requests.cancel', $req->WC_REQ_CODE));

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.my'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'CANCELLED',
        ]);
    }

    public function test_ward_nurse_cannot_cancel_handled_referral(): void
    {
        $req = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'خالد عبد الله سعيد',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->post(route('requests.cancel', $req->WC_REQ_CODE));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_CODE' => $req->WC_REQ_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
        ]);
    }
}
