<?php

namespace Tests\Feature;

use App\Enums\EmployeeRole;
use App\Models\Employee;
use App\Models\JobLookup;
use App\Models\User;
use App\Rules\HospitalEmployeeExists;
use App\Support\LocalHospitalCopy;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The employee pick lists, and the validation behind them, follow the job (EMP_JOB_CD) of each field's role:
 * doctors in the doctor fields, nurses in the nurse fields, both in the wound care team field.
 */
class EmployeePickListsTest extends TestCase
{
    use RefreshDatabase;

    private const DOCTOR_JOB = 2;

    private const NURSE_JOB = 31;

    private const CLERK_JOB = 55;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'hospital.hospital_no' => 1,
            'hospital.employees.job_codes.doctors' => (string) self::DOCTOR_JOB,
            'hospital.employees.job_codes.nurses' => self::NURSE_JOB.', 32',
        ]);
    }

    public function test_each_list_holds_the_active_employees_of_its_jobs_in_this_hospital(): void
    {
        $doctor = $this->employee('طبيب', self::DOCTOR_JOB);
        $nurse = $this->employee('ممرض', self::NURSE_JOB);
        $headNurse = $this->employee('رئيس تمريض', 32);
        $this->employee('كاتب', self::CLERK_JOB);
        $this->employee('بلا وظيفة', null);
        Employee::factory()->inactive()->create(['EMP_JOB_CD' => self::DOCTOR_JOB]);
        Employee::factory()->forHospital(2)->create(['EMP_JOB_CD' => self::DOCTOR_JOB]);

        $lists = Employee::pickLists();

        $this->assertSame([$doctor->EMP_SERIAL], $lists['doctors']->modelKeys());
        $this->assertEqualsCanonicalizing([$nurse->EMP_SERIAL, $headNurse->EMP_SERIAL], $lists['nurses']->modelKeys());
        $this->assertEqualsCanonicalizing(
            [$doctor->EMP_SERIAL, $nurse->EMP_SERIAL, $headNurse->EMP_SERIAL],
            $lists['woundCareTeam']->modelKeys()
        );
    }

    public function test_a_role_without_configured_codes_lists_every_active_employee(): void
    {
        config(['hospital.employees.job_codes.nurses' => '']);
        $doctor = $this->employee('طبيب', self::DOCTOR_JOB);
        $clerk = $this->employee('كاتب', self::CLERK_JOB);

        $lists = Employee::pickLists();

        $this->assertSame([$doctor->EMP_SERIAL], $lists['doctors']->modelKeys());
        $this->assertEqualsCanonicalizing([$doctor->EMP_SERIAL, $clerk->EMP_SERIAL], $lists['nurses']->modelKeys());
        $this->assertEqualsCanonicalizing([$doctor->EMP_SERIAL, $clerk->EMP_SERIAL], $lists['woundCareTeam']->modelKeys());
        $this->assertSame(['الممرضين', 'فريق الجروح'], $lists['unfilteredRoles']);
    }

    public function test_without_the_setting_nursing_jobs_are_found_by_name_and_logged(): void
    {
        config(['hospital.employees.job_codes.nurses' => '']);
        $this->job(31, 'ممرّضة', null);
        $this->job(40, 'مشرف تمريض', null);
        $this->job(41, null, 'Staff NURSE');
        $this->job(55, 'كاتب', 'Clerk');
        DB::table((new JobLookup)->getTable())->insert(['JOB_CODE' => 70, 'JOB_NAME_AR' => 'ممرض', 'HOS_NO' => 2]);
        $nurse = $this->employee('ممرضة', 31);
        $supervisor = $this->employee('مشرف', 40);
        $staffNurse = $this->employee('ممرض', 41);
        $this->employee('كاتب', 55);
        Log::spy();

        $lists = Employee::pickLists();

        $this->assertEqualsCanonicalizing([31, 40, 41], EmployeeRole::Nurse->jobCodes());
        $this->assertEqualsCanonicalizing([$nurse->EMP_SERIAL, $supervisor->EMP_SERIAL, $staffNurse->EMP_SERIAL], $lists['nurses']->modelKeys());
        $this->assertSame([], $lists['unfilteredRoles']);
        Log::shouldHaveReceived('info')->once()->withArgs(fn (string $message) => str_contains($message, 'HOSPITAL_NURSE_JOB_CODES فارغ')
            && str_contains($message, '31 (ممرّضة)') && str_contains($message, '40 (مشرف تمريض)') && str_contains($message, '41 (Staff NURSE)')
            && ! str_contains($message, '55') && ! str_contains($message, '70'));
    }

    public function test_the_setting_overrides_the_detection(): void
    {
        config(['hospital.employees.job_codes.nurses' => '55']);
        $this->job(31, 'ممرض', 'Nurse');
        $clerk = $this->employee('كاتب', 55);
        $this->employee('ممرض', 31);

        $this->assertSame([55], EmployeeRole::Nurse->jobCodes());
        $this->assertSame([$clerk->EMP_SERIAL], Employee::pickLists()['nurses']->modelKeys());
    }

    public function test_when_no_nursing_job_is_found_the_lists_are_unfiltered_loudly(): void
    {
        config(['hospital.employees.job_codes.nurses' => '']);
        $this->employee('كاتب الاستقبال', self::CLERK_JOB);
        Log::spy();

        $lists = Employee::pickLists();

        $this->assertSame(['الممرضين', 'فريق الجروح'], $lists['unfilteredRoles']);
        Log::shouldHaveReceived('warning')->withArgs(fn (string $message) => str_contains($message, 'لم توجد في C_JOB_TB')
            && str_contains($message, 'HOSPITAL_NURSE_JOB_CODES'));

        $this->call('GET', route('requests.create'), [], ['hospital_token' => $this->hospitalToken()])
            ->assertOk()
            ->assertSee('كاتب الاستقبال')
            ->assertSee('تعرض كل الموظفين النشطين');
    }

    public function test_doctor_lists_follow_the_hospitals_record_status(): void
    {
        $current = $this->employee('طبيب حالي', self::DOCTOR_JOB);
        $former = $this->employee('طبيب سابق', self::DOCTOR_JOB, recordStatus: 2);
        $nurseWithOtherStatus = $this->employee('ممرض', self::NURSE_JOB, recordStatus: 2);
        $doctorRule = ['code' => [new HospitalEmployeeExists(EmployeeRole::Doctor)]];

        $lists = Employee::pickLists();
        $this->assertSame([$current->EMP_SERIAL], $lists['doctors']->modelKeys());
        $this->assertSame([$nurseWithOtherStatus->EMP_SERIAL], $lists['nurses']->modelKeys());
        $this->assertFalse(Validator::make(['code' => $former->EMP_SERIAL], $doctorRule)->passes());

        config(['hospital.employees.record_status_lists' => 'all']);
        $this->assertSame([], Employee::pickLists()['nurses']->modelKeys());

        config(['hospital.employees.record_status' => '', 'hospital.employees.record_status_lists' => 'doctors']);
        $this->assertEqualsCanonicalizing([$current->EMP_SERIAL, $former->EMP_SERIAL], Employee::pickLists()['doctors']->modelKeys());
    }

    public function test_validation_rejects_an_employee_outside_the_fields_role(): void
    {
        $doctor = $this->employee('طبيب', self::DOCTOR_JOB);
        $nurse = $this->employee('ممرض', self::NURSE_JOB);
        $clerk = $this->employee('كاتب', self::CLERK_JOB);

        $passes = fn (EmployeeRole $role, Employee $employee): bool => Validator::make(
            ['code' => $employee->EMP_SERIAL],
            ['code' => [new HospitalEmployeeExists($role)]]
        )->passes();

        $this->assertTrue($passes(EmployeeRole::Doctor, $doctor));
        $this->assertFalse($passes(EmployeeRole::Doctor, $nurse));
        $this->assertTrue($passes(EmployeeRole::Nurse, $nurse));
        $this->assertFalse($passes(EmployeeRole::Nurse, $doctor));
        $this->assertTrue($passes(EmployeeRole::WoundCareTeam, $doctor));
        $this->assertTrue($passes(EmployeeRole::WoundCareTeam, $nurse));
        $this->assertFalse($passes(EmployeeRole::WoundCareTeam, $clerk));

        $message = Validator::make(['code' => $nurse->EMP_SERIAL], ['code' => [new HospitalEmployeeExists(EmployeeRole::Doctor)]])
            ->errors()->first('code');
        $this->assertStringContainsString('ليس من الأطباء', $message);
    }

    public function test_the_referral_form_lists_only_nurses(): void
    {
        $this->employee('ممرضة القسم', self::NURSE_JOB);
        $this->employee('طبيب الجراحة', self::DOCTOR_JOB);
        $this->employee('كاتب الاستقبال', self::CLERK_JOB);

        $this->call('GET', route('requests.create'), [], ['hospital_token' => $this->hospitalToken()])
            ->assertOk()
            ->assertSee('ممرضة القسم')
            ->assertDontSee('طبيب الجراحة')
            ->assertDontSee('كاتب الاستقبال');
    }

    private function employee(string $firstName, ?int $jobCode, int $recordStatus = 1): Employee
    {
        if ($jobCode !== null) {
            LocalHospitalCopy::firstOrInsert(JobLookup::class, ['JOB_CODE' => $jobCode], ['JOB_NAME_AR' => 'وظيفة '.$jobCode]);
        }

        return Employee::factory()->named($firstName, null, null, 'الاختبار')
            ->create(['EMP_JOB_CD' => $jobCode, 'EMP_REC_CD' => $recordStatus]);
    }

    private function job(int $code, ?string $nameAr, ?string $nameEn): void
    {
        LocalHospitalCopy::firstOrInsert(JobLookup::class, ['JOB_CODE' => $code], ['JOB_NAME_AR' => $nameAr, 'JOB_NAME_EN' => $nameEn]);
    }

    private function hospitalToken(): string
    {
        $user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(), 'PER_USER_USERNAME' => 'jobs_'.Str::random(8),
            'PER_USER_ROLE' => 'ADMIN', 'PER_USER_IS_ACTIVE' => 1,
        ]);

        return JWT::encode(
            ['sub' => $user->PER_USER_USERNAME, 'iat' => time(), 'exp' => time() + 3600],
            config('services.hospital_auth.jwt_secret'),
            'HS256'
        );
    }
}
