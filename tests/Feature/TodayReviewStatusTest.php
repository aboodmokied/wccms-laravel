<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\PerUser;
use App\Models\WcDailyReview;
use App\Models\WcDepartment;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TodayReviewStatusTest extends TestCase
{
    use RefreshDatabase;

    protected PerUser $user;

    protected PerEmployee $nurse;

    protected MrPatient $patient;

    protected WcDepartment $department;

    protected WcRequestReg $request;

    protected WcRegistration $registration;

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

        $this->nurse = PerEmployee::create([
            'PER_EMP_CODE' => (string) Str::uuid(),
            'PER_EMP_FULL_NAME' => 'الممرض التجريبي',
            'PER_EMP_IS_ACTIVE' => 1,
        ]);

        $this->user->update(['PER_USER_EMP_CD' => $this->nurse->PER_EMP_CODE]);

        $this->patient = MrPatient::create([
            'MR_PAT_CODE' => (string) Str::uuid(),
            'MR_PAT_ID_NO' => '999888777',
            'MR_PAT_FULL_NAME' => 'مريض تجربة المراجعة اليومية',
            'MR_PAT_GENDER' => 'MALE',
        ]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم الجراحة العامة',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $this->request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $this->patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $this->patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->nurse->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $this->registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $this->request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurse->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
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

    public function test_registration_shows_not_reviewed_today_when_no_daily_review_exists(): void
    {
        $token = $this->generateJwt('test_nurse');
        $this->assertFalse($this->registration->isReviewedToday());

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get(route('registrations.show', $this->registration->WC_REG_CODE));

        $response->assertStatus(200);
        $response->assertSee('لم تُجرَ مراجعة اليوم');
        $response->assertSee('لم يراجع اليوم');
    }

    public function test_registration_shows_reviewed_today_when_daily_review_added_today(): void
    {
        $token = $this->generateJwt('test_nurse');

        WcDailyReview::create([
            'WC_DREV_CODE' => (string) Str::uuid(),
            'WC_DREV_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_DREV_VISIT_DATE' => now(),
            'WC_DREV_NURSE_CD' => $this->nurse->PER_EMP_CODE,
            'WC_DREV_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_FOLLOWUP',
            'WC_DREV_GENERAL_NOTES' => 'مراجعة اليوم المكتملة',
        ]);

        $this->assertTrue($this->registration->fresh()->isReviewedToday());

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get(route('registrations.show', $this->registration->WC_REG_CODE));

        $response->assertStatus(200);
        $response->assertSee('تمت مراجعته اليوم');
    }
}
