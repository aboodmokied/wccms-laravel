<?php

namespace Tests\Feature;

use App\Models\HospitalInfo;
use App\Models\JobLookup;
use App\Models\SexLookup;
use App\Support\HospitalStartupCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class HospitalStartupCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['hospital.hospital_no' => 1]);
    }

    public function test_logs_the_hospital_number_and_confirms_a_matching_current_hospital(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: true);
        $this->hospital(2, 'مستشفى الرنتيسي', current: false);
        SexLookup::factory()->create();
        SexLookup::factory()->female()->create();
        config(['hospital.employees.job_codes' => ['doctors' => '2', 'nurses' => '31']]);
        $this->job(2, 'طبيب');
        $this->job(31, 'ممرض');
        Log::spy();

        $findings = app(HospitalStartupCheck::class)->run();

        $this->assertSame([], $this->warnings($findings));
        Log::shouldHaveReceived('log')->withArgs(fn (string $level, string $message) => $level === 'info' && str_contains($message, 'HOSPITAL_NO=1'));
        Log::shouldNotHaveReceived('log', ['warning', \Mockery::any()]);
    }

    public function test_warns_naming_both_numbers_when_the_database_belongs_to_another_hospital(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: false);
        $this->hospital(5, 'مجمع ناصر الطبي', current: true);

        $warnings = $this->warnings(app(HospitalStartupCheck::class)->run());

        $this->assertCount(1, array_filter($warnings, fn (string $m) => str_contains($m, 'HOSPITAL_NO المضبوط هو 1') && str_contains($m, 'للمستشفى 5')));
    }

    public function test_keeps_running_and_logs_when_the_hospitals_table_cannot_be_read(): void
    {
        config(['hospital.tables.hospitals' => 'NO_SUCH_HOSPITAL_TB']);
        Log::spy();

        $this->artisan('wccms:check-hospital')
            ->expectsOutputToContain('تعذرت قراءة NO_SUCH_HOSPITAL_TB')
            ->assertSuccessful();

        Log::shouldHaveReceived('log')->withArgs(fn (string $level, string $message) => $level === 'warning' && str_contains($message, 'NO_SUCH_HOSPITAL_TB'));
    }

    public function test_warns_with_the_codes_whose_english_sex_name_is_not_exactly_male_or_female(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: true);
        SexLookup::factory()->create(['SEX_CODE' => 1, 'SEX_NAME_EN' => 'Female']);
        SexLookup::factory()->create(['SEX_CODE' => 2, 'SEX_NAME_EN' => 'MALE']);
        SexLookup::factory()->create(['SEX_CODE' => 3, 'SEX_NAME_EN' => null]);
        // Another hospital's odd codes are not this hospital's problem.
        SexLookup::factory()->forHospital(2)->create(['SEX_CODE' => 7, 'SEX_NAME_EN' => 'x']);

        $warnings = array_values(array_filter(
            $this->warnings(app(HospitalStartupCheck::class)->run()),
            fn (string $m) => str_contains($m, 'SEX_NAME_EN')
        ));

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString("2 (SEX_NAME_EN='MALE')", $warnings[0]);
        $this->assertStringContainsString('3 (SEX_NAME_EN=NULL)', $warnings[0]);
        $this->assertStringNotContainsString('1 (', $warnings[0]);
        $this->assertStringNotContainsString('7 (', $warnings[0]);
    }

    public function test_warns_when_no_hospital_is_marked_current(): void
    {
        $this->hospital(1, 'مجمع الشفاء الطبي', current: false);

        $warnings = $this->warnings(app(HospitalStartupCheck::class)->run());

        $this->assertNotEmpty(array_filter($warnings, fn (string $m) => str_contains($m, 'H_CURR = 1')));
    }

    public function test_names_the_jobs_of_the_pick_lists_and_warns_about_unknown_or_missing_codes(): void
    {
        config(['hospital.employees.job_codes' => ['doctors' => '2, 9', 'nurses' => '']]);
        $this->job(2, 'طبيب');
        // Another hospital's job 9 does not make the code valid here.
        DB::table((new JobLookup)->getTable())->insert(['JOB_CODE' => 9, 'JOB_NAME_AR' => 'طبيب مقيم', 'HOS_NO' => 2]);

        $findings = app(HospitalStartupCheck::class)->run();
        $warnings = $this->warnings($findings);
        $infos = array_column(array_filter($findings, fn (array $f) => $f['level'] === HospitalStartupCheck::INFO), 'message');

        $this->assertNotEmpty(array_filter($infos, fn (string $m) => str_contains($m, 'قوائم الأطباء') && str_contains($m, '2 (طبيب)')));
        $this->assertNotEmpty(array_filter($warnings, fn (string $m) => str_contains($m, 'أكواد وظائف الأطباء غير موجودة') && str_contains($m, ': 9 (')));
        $this->assertNotEmpty(array_filter($warnings, fn (string $m) => str_contains($m, 'HOSPITAL_NURSE_JOB_CODES')));
    }

    private function job(int $code, string $name): void
    {
        DB::table((new JobLookup)->getTable())->insert(['JOB_CODE' => $code, 'JOB_NAME_AR' => $name, 'HOS_NO' => 1]);
    }

    private function hospital(int $code, string $name, bool $current): void
    {
        DB::table((new HospitalInfo)->getTable())->insert([
            'H_CODE' => $code, 'H_NAME_AR' => $name, 'H_CURR' => $current ? 1 : 0, 'HOS_NO' => $code,
        ]);
    }

    /**
     * @param  list<array{level: string, message: string}>  $findings
     * @return list<string>
     */
    private function warnings(array $findings): array
    {
        return array_values(array_map(
            fn (array $finding) => $finding['message'],
            array_filter($findings, fn (array $finding) => $finding['level'] === HospitalStartupCheck::WARNING)
        ));
    }
}
