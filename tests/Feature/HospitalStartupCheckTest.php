<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\HospitalInfo;
use App\Models\JobLookup;
use App\Models\SexLookup;
use App\Support\HospitalStartupCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * wccms:check-hospital, the deployment checklist: read only, never stops WCCMS, every warning with its fix.
 */
class HospitalStartupCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'hospital.hospital_no' => 1,
            'hospital.employees.job_codes' => ['doctors' => '2', 'nurses' => ''],
        ]);
    }

    public function test_a_complete_hospital_gives_no_warning_and_logs_every_finding(): void
    {
        $this->completeHospital();
        Log::spy();

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertSame([], $this->warnings($findings));
        $this->assertFinding($findings, 'HOSPITAL_NO=1');
        $this->assertFinding($findings, '1 — مجمع الشفاء الطبي');
        Log::shouldHaveReceived('log')->withArgs(fn (string $level, string $message) => $level === 'info' && str_contains($message, 'HOSPITAL_NO=1'));
        Log::shouldNotHaveReceived('log', ['warning', \Mockery::any()]);
    }

    public function test_the_command_prints_the_checklist_in_arabic(): void
    {
        $this->completeHospital();

        $this->artisan('wccms:check-hospital')
            ->expectsOutputToContain('قائمة فحص التسليم')
            ->expectsOutputToContain('المستشفى الحالي في C_HOSPITAL_TB (H_CURR = 1): 1 — مجمع الشفاء الطبي')
            ->expectsOutputToContain("الرمز 1: انثى / 'Female' — التصنيف: أنثى")
            ->expectsOutputToContain("الرمز 2: ذكر / 'Male' — التصنيف: ذكر")
            ->expectsOutputToContain('قوائم الأطباء (من الإعداد HOSPITAL_DOCTOR_JOB_CODES) تعرض الوظائف: 2 (طبيب / Doctor)')
            ->expectsOutputToContain('اكتُشفت وظائف التمريض تلقائياً بالأسماء (تمريض، ممرض، ممرضة، Nurse، Nursing): 31 (ممرض / Nurse)')
            ->expectsOutputToContain('HOSPITAL_NURSE_JOB_CODES=31')
            ->expectsOutputToContain('الممرضون النشطون بالوظيفة: 1؛ بـ USER_TYPE = 3: 1؛ في الاثنين معاً: 1.')
            ->expectsOutputToContain('وظائف موظفي USER_TYPE = 3: 31 (ممرض / Nurse) ×1.')
            ->expectsOutputToContain('الموظف النشط: بلا شرط على EMP_ACTIVE في كل القوائم؛ EMP_REC_CD = 1 في كل القوائم.')
            ->expectsOutputToContain('عدد الأطباء النشطين: 1')
            ->expectsOutputToContain('عدد الممرضين النشطين: 1')
            ->expectsOutputToContain('التحذيرات: 0')
            ->assertSuccessful();
    }

    public function test_warns_naming_both_numbers_when_the_database_belongs_to_another_hospital(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: false);
        $this->hospital(5, 'مجمع ناصر الطبي', current: true);

        $warning = $this->warningContaining(app(HospitalStartupCheck::class)->run(), 'HOSPITAL_NO المضبوط هو 1');

        $this->assertStringContainsString('للمستشفى 5', $warning['message']);
        $this->assertStringContainsString('HOSPITAL_NO=5', $warning['fix']);
    }

    public function test_keeps_running_and_logs_when_the_hospitals_table_cannot_be_read(): void
    {
        config(['hospital.tables.hospitals' => 'NO_SUCH_HOSPITAL_TB']);
        Log::spy();

        $this->artisan('wccms:check-hospital')
            ->expectsOutputToContain('تعذرت قراءة NO_SUCH_HOSPITAL_TB')
            ->expectsOutputToContain('GRANT SELECT')
            ->assertSuccessful();

        Log::shouldHaveReceived('log')->withArgs(fn (string $level, string $message) => $level === 'warning' && str_contains($message, 'NO_SUCH_HOSPITAL_TB'));
    }

    public function test_lists_every_sex_code_and_warns_about_the_ones_not_exactly_male_or_female(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: true);
        SexLookup::factory()->create(['SEX_CODE' => 1, 'SEX_NAME_AR' => 'انثى', 'SEX_NAME_EN' => 'Female']);
        SexLookup::factory()->create(['SEX_CODE' => 2, 'SEX_NAME_AR' => 'ذكر', 'SEX_NAME_EN' => 'MALE']);
        SexLookup::factory()->create(['SEX_CODE' => 3, 'SEX_NAME_EN' => null]);
        // Another hospital's odd codes are not this hospital's problem.
        SexLookup::factory()->forHospital(2)->create(['SEX_CODE' => 7, 'SEX_NAME_EN' => 'x']);

        $findings = app(HospitalStartupCheck::class)->run();
        $warning = $this->warningContaining($findings, 'SEX_NAME_EN');

        $this->assertStringContainsString("2 (SEX_NAME_EN='MALE')", $warning['message']);
        $this->assertStringContainsString('3 (SEX_NAME_EN=NULL)', $warning['message']);
        $this->assertStringNotContainsString('1 (', $warning['message']);
        $this->assertStringNotContainsString('7 (', $warning['message']);
        $this->assertStringContainsString('HOSPITAL_SEX_MALE_NAME_EN', $warning['fix']);
        $this->assertFinding($findings, "الرمز 1: انثى / 'Female' — التصنيف: أنثى");
        $this->assertFinding($findings, "الرمز 2: ذكر / 'MALE' — التصنيف: غير معروف");
    }

    public function test_warns_when_no_hospital_is_marked_current(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: false);

        $this->warningContaining(app(HospitalStartupCheck::class)->run(), 'H_CURR = 1');
    }

    public function test_names_the_configured_job_codes_and_warns_about_missing_ones_with_the_full_job_list(): void
    {
        config(['hospital.employees.job_codes' => ['doctors' => '2, 9', 'nurses' => '31']]);
        $this->job(2, 'طبيب', 'Doctor');
        $this->job(31, 'ممرض', 'Nurse');
        $this->job(55, 'كاتب', 'Clerk');
        // Another hospital's job 9 does not make the code valid here.
        DB::table((new JobLookup)->getTable())->insert(['JOB_CODE' => 9, 'JOB_NAME_AR' => 'طبيب مقيم', 'HOS_NO' => 2]);

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertFinding($findings, 'تعرض الوظائف: 2 (طبيب / Doctor)');
        $this->assertFinding($findings, 'قوائم الممرضين (من الإعداد HOSPITAL_NURSE_JOB_CODES) تعرض الوظائف: 31 (ممرض / Nurse)');
        $warning = $this->warningContaining($findings, 'أكواد وظائف الأطباء غير موجودة');
        $this->assertStringContainsString(': 9.', $warning['message']);
        $this->assertStringContainsString('HOSPITAL_DOCTOR_JOB_CODES', $warning['fix']);
        $this->assertFinding($findings, '55 (كاتب / Clerk)');
    }

    public function test_warns_when_no_nursing_job_is_found_and_says_what_to_set(): void
    {
        $this->job(2, 'طبيب', 'Doctor');
        $this->job(55, 'كاتب', 'Clerk');

        $warning = $this->warningContaining(app(HospitalStartupCheck::class)->run(), 'لم توجد في C_JOB_TB');

        $this->assertStringContainsString('كل الموظفين النشطين', $warning['message']);
        $this->assertStringContainsString('HOSPITAL_NURSE_JOB_CODES', $warning['fix']);
    }

    public function test_warns_when_a_list_has_no_active_employee(): void
    {
        $this->completeHospital();
        Employee::query()->get()->each(fn (Employee $employee) => DB::table($employee->getTable())
            ->where(['EMP_SERIAL' => $employee->EMP_SERIAL, 'HOS_NO' => 1])
            ->update(['EMP_REC_CD' => 0]));

        $warning = $this->warningContaining(app(HospitalStartupCheck::class)->run(), 'لا يوجد أي موظف نشط في قوائم الأطباء');

        $this->assertStringContainsString('HOSPITAL_EMP_REC_CD', $warning['fix']);
    }

    public function test_warns_when_the_nursing_jobs_and_user_type_differ_a_lot(): void
    {
        $this->completeHospital();
        $this->job(40, 'فني عناية', 'Care technician');
        Employee::factory()->count(3)->create(['EMP_JOB_CD' => 40, 'USER_TYPE' => 3]);
        // Not counted: another hospital's employee, and a former employee.
        Employee::factory()->forHospital(2)->create(['EMP_JOB_CD' => 40, 'USER_TYPE' => 3]);
        Employee::factory()->create(['EMP_JOB_CD' => 40, 'USER_TYPE' => 3, 'EMP_REC_CD' => 0]);

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertFinding($findings, 'الممرضون النشطون بالوظيفة: 1؛ بـ USER_TYPE = 3: 4؛ في الاثنين معاً: 1.');
        $this->assertFinding($findings, '40 (فني عناية / Care technician) ×3، 31 (ممرض / Nurse) ×1');
        $warning = $this->warningContaining($findings, 'يختلف الممرضون بالوظيفة (1) عن موظفي USER_TYPE = 3 (4) كثيراً');
        $this->assertStringContainsString('المشترك 1 فقط (25%', $warning['message']);
        $this->assertStringContainsString('HOSPITAL_NURSE_JOB_CODES=31,40', $warning['fix']);
        $this->assertStringContainsString('HOSPITAL_NURSE_USER_TYPE=', $warning['fix']);
    }

    public function test_a_small_difference_with_user_type_is_not_a_warning(): void
    {
        $this->completeHospital();
        Employee::factory()->count(4)->create(['EMP_JOB_CD' => 31, 'USER_TYPE' => 3]);
        Employee::factory()->create(['EMP_JOB_CD' => 31, 'USER_TYPE' => null]);

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertFinding($findings, 'الممرضون النشطون بالوظيفة: 6؛ بـ USER_TYPE = 3: 5؛ في الاثنين معاً: 5.');
        $this->assertSame([], $this->warnings($findings));
    }

    public function test_the_user_type_comparison_can_be_turned_off(): void
    {
        $this->completeHospital();
        Employee::factory()->count(3)->create(['EMP_JOB_CD' => 2, 'USER_TYPE' => 3]);
        config(['hospital.employees.nurse_user_type' => '']);

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertFinding($findings, 'المقارنة مع USER_TYPE موقوفة');
        $this->assertSame([], $this->warnings($findings));
    }

    private function completeHospital(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: true);
        $this->hospital(2, 'مستشفى الرنتيسي', current: false);
        SexLookup::factory()->create(['SEX_CODE' => 2, 'SEX_NAME_AR' => 'ذكر', 'SEX_NAME_EN' => 'Male']);
        SexLookup::factory()->create(['SEX_CODE' => 1, 'SEX_NAME_AR' => 'انثى', 'SEX_NAME_EN' => 'Female']);
        $this->job(2, 'طبيب', 'Doctor');
        $this->job(31, 'ممرض', 'Nurse');
        Employee::factory()->create(['EMP_JOB_CD' => 2]);
        Employee::factory()->create(['EMP_JOB_CD' => 31, 'USER_TYPE' => 3]);
    }

    private function hospital(int $code, string $name, bool $current): void
    {
        DB::table((new HospitalInfo)->getTable())->insert([
            'H_CODE' => $code, 'H_NAME_AR' => $name, 'H_CURR' => $current ? 1 : 0, 'HOS_NO' => $code,
        ]);
    }

    private function job(int $code, string $nameAr, string $nameEn): void
    {
        DB::table((new JobLookup)->getTable())->insert(['JOB_CODE' => $code, 'JOB_NAME_AR' => $nameAr, 'JOB_NAME_EN' => $nameEn, 'HOS_NO' => 1]);
    }

    /**
     * @param  list<array{level: string, section: string, message: string, fix: ?string}>  $findings
     * @return list<array{level: string, section: string, message: string, fix: ?string}>
     */
    private function warnings(array $findings): array
    {
        return array_values(array_filter($findings, fn (array $finding) => $finding['level'] === HospitalStartupCheck::WARNING));
    }

    /**
     * @param  list<array{level: string, section: string, message: string, fix: ?string}>  $findings
     * @return array{level: string, section: string, message: string, fix: ?string}
     */
    private function warningContaining(array $findings, string $text): array
    {
        $matches = array_values(array_filter($this->warnings($findings), fn (array $w) => str_contains($w['message'], $text)));
        $this->assertCount(1, $matches, "One warning containing \"{$text}\". Warnings: ".json_encode(array_column($this->warnings($findings), 'message'), JSON_UNESCAPED_UNICODE));
        $this->assertNotEmpty($matches[0]['fix']);

        return $matches[0];
    }

    /**
     * @param  list<array{level: string, section: string, message: string, fix: ?string}>  $findings
     */
    private function assertFinding(array $findings, string $text): void
    {
        $this->assertNotEmpty(
            array_filter($findings, fn (array $f) => str_contains($f['message'], $text)),
            "A finding containing \"{$text}\". Findings: ".json_encode(array_column($findings, 'message'), JSON_UNESCAPED_UNICODE)
        );
    }
}
