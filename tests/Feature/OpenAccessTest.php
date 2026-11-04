<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Support\SystemUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WCCMS has no sign-in: the hospital controls who reaches it. Pages open directly and every row records the one
 * system account as "entered by".
 */
class OpenAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_dashboard_open_without_any_sign_in(): void
    {
        $this->get('/')->assertOk();
        $this->get('/dashboard')->assertOk();
        $this->get('/admin')->assertOk();
    }

    public function test_sign_in_routes_no_longer_exist(): void
    {
        $this->get('/login')->assertNotFound();
        $this->post('/logout')->assertNotFound();
        $this->get('/mock-hospital-auth')->assertNotFound();
        $this->get('/admin/users')->assertNotFound();
    }

    public function test_a_referral_is_entered_by_the_system_account_created_on_demand(): void
    {
        $this->assertFalse(User::query()->whereKey(SystemUser::CODE)->exists());

        $nurse = Employee::factory()->named('ممرض القسم')->create();
        $patient = MrPatient::factory()->create();

        $this->post(route('requests.store'), [
            'patient_code' => $patient->MRP_PATREC_CODE,
            'attending_nurse_code' => $nurse->EMP_SERIAL,
        ])->assertRedirect(route('requests.my'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_NURSE_CD' => $nurse->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => SystemUser::CODE,
        ]);
        $this->assertDatabaseHas('wc_users_tb', [
            'PER_USER_CODE' => SystemUser::CODE,
            'PER_USER_USERNAME' => SystemUser::USERNAME,
        ]);
        $this->assertSame(1, User::query()->count());
    }
}
