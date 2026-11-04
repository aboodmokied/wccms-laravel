<?php

namespace Tests\Feature;

use App\Exceptions\HospitalLinkException;
use App\Models\AdmissionPatient;
use App\Models\Employee;
use App\Models\HospitalLocation;
use App\Models\InpatientStay;
use App\Models\MrPatient;
use App\Models\SexLookup;
use App\Models\User;
use App\Models\WcDailyReview;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Rules\HospitalEmployeeExists;
use App\Rules\HospitalPatientExists;
use App\Services\HospitalPatientSyncService;
use App\Services\ReportingService;
use App\Services\Reports\MonthlyCaseReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * WCCMS runs for one hospital (config hospital.hospital_no = 1 here) on a database that holds several. Hospital 2
 * below reuses the same patient, employee, ward, sex and admission codes with different data: nothing of it may
 * reach a screen, a relation, a validation, a save or a report.
 */
class HospitalIsolationTest extends TestCase
{
    use RefreshDatabase;

    private const OTHER = 2;

    protected function setUp(): void
    {
        parent::setUp();
        config(['hospital.hospital_no' => 1]);
    }

    public function test_refreshing_a_hospital_row_reads_that_hospitals_row(): void
    {
        $own = Employee::factory()->named('أحمد', 'علي', null, 'سالم')->create(['EMP_SERIAL' => 7001]);
        Employee::factory()->forHospital(self::OTHER)->named('خالد', 'محمد', null, 'يوسف')->create(['EMP_SERIAL' => 7001]);

        $this->assertSame('أحمد', $own->fresh()->EMP_FIRST_NAME_AR);
        $this->assertSame('أحمد', $own->refresh()->EMP_FIRST_NAME_AR);
    }

    public function test_patient_search_and_number_lookup_return_only_the_configured_hospital(): void
    {
        $own = MrPatient::factory()->named('سامي', 'خليل', 'محمود', 'حلس')->create(['MRP_PATREC_CODE' => 5001, 'MRP_ID' => 900000001]);
        MrPatient::factory()->forHospital(self::OTHER)->named('سامي', 'خليل', 'محمود', 'حلس')->create(['MRP_PATREC_CODE' => 5001, 'MRP_ID' => 900000001]);
        MrPatient::factory()->forHospital(self::OTHER)->named('سامي', 'آخر', null, 'حلس')->create(['MRP_PATREC_CODE' => 5002, 'MRP_ID' => 900000002]);

        $byName = MrPatient::query()->matching('سامي حلس')->get();
        $this->assertSame([[5001, 1]], $byName->map(fn (MrPatient $p) => [$p->MRP_PATREC_CODE, (int) $p->HOS_NO])->all());

        $this->assertSame(1, MrPatient::query()->byNumber('900000001')->count());
        $this->assertSame(0, MrPatient::query()->byNumber('900000002')->count());
        $this->assertSame(0, MrPatient::query()->byNumber('5002')->count());
        $this->assertTrue(MrPatient::query()->byNumber('5001')->first()->is($own));

        // Non-numeric input never reaches the NUMBER columns (ORA-01722 on Oracle).
        $this->assertSame(0, MrPatient::query()->byNumber('12a')->count());
        $this->assertSame(0, MrPatient::query()->byNumber('1234567890')->count());
        $this->assertNull(MrPatient::query()->find(5002));
    }

    public function test_hospital_lookup_service_never_returns_another_hospitals_patient(): void
    {
        MrPatient::factory()->forHospital(self::OTHER)->named('مريض', null, null, 'بعيد')->create(['MRP_PATREC_CODE' => 7001, 'MRP_ID' => 911111111]);

        $service = new HospitalPatientSyncService;
        $this->assertFalse($service->searchAndSyncPatient('911111111')['found']);
        $this->assertFalse($service->searchAndSyncPatient('7001')['found']);
        $this->assertFalse($service->searchAndSyncPatient('بعيد')['found']);
    }

    public function test_employee_pick_lists_show_active_employees_of_the_configured_hospital_only(): void
    {
        $nurse = Employee::factory()->named('ممرضة', null, null, 'المستشفى')->create(['EMP_SERIAL' => 3001]);
        Employee::factory()->inactive()->named('متقاعد', null, null, 'سابق')->create(['EMP_SERIAL' => 3002]);
        Employee::factory()->forHospital(self::OTHER)->named('غريب', null, null, 'تماماً')->create(['EMP_SERIAL' => 3003]);
        Employee::factory()->forHospital(self::OTHER)->named('مكرر', null, null, 'الرقم')->create(['EMP_SERIAL' => 3001]);

        $listed = Employee::query()->active()->orderedByName()->get();

        $this->assertSame([3001], $listed->pluck('EMP_SERIAL')->all());
        $this->assertSame($nurse->full_name, $listed->first()->full_name);

        $response = $this->get(route('requests.create'));
        $response->assertOk()
            ->assertSee('ممرضة المستشفى')
            ->assertDontSee('غريب تماماً')
            ->assertDontSee('مكرر الرقم')
            ->assertDontSee('متقاعد سابق');
    }

    public function test_relations_resolve_codes_inside_the_configured_hospital(): void
    {
        [$patient, $nurse, $registration] = $this->ownRegistration();
        MrPatient::factory()->forHospital(self::OTHER)->named('اسم', null, null, 'خاطئ')->create(['MRP_PATREC_CODE' => $patient->MRP_PATREC_CODE]);
        Employee::factory()->forHospital(self::OTHER)->named('موظف', null, null, 'خاطئ')->create(['EMP_SERIAL' => $nurse->EMP_SERIAL]);

        $fresh = WcRegistration::query()->with(['patient', 'nurse'])->findOrFail($registration->WC_REG_CODE);

        $this->assertSame($patient->full_name, $fresh->patient->full_name);
        $this->assertSame(1, (int) $fresh->patient->HOS_NO);
        $this->assertSame($nurse->full_name, $fresh->nurse->full_name);
        $this->assertSame(1, (int) $fresh->nurse->HOS_NO);
        $this->assertSame($patient->full_name, $registration->fresh()->patient->full_name);
    }

    public function test_current_admission_ward_and_doctor_come_from_the_configured_hospital(): void
    {
        $patient = MrPatient::factory()->create(['MRP_PATREC_CODE' => 6001]);
        $doctor = Employee::factory()->named('د.', 'سمير', null, 'الطبيب')->create(['EMP_SERIAL' => 4001]);
        HospitalLocation::factory()->create(['LOC_CODE' => 10, 'LOC_NAME_AR' => 'الجراحة رجال']);
        HospitalLocation::factory()->create(['LOC_CODE' => 11, 'LOC_NAME_AR' => 'الاستقبال']);
        HospitalLocation::factory()->forHospital(self::OTHER)->create(['LOC_CODE' => 10, 'LOC_NAME_AR' => 'قسم مستشفى آخر']);
        Employee::factory()->forHospital(self::OTHER)->named('طبيب', null, null, 'آخر')->create(['EMP_SERIAL' => 4001]);

        $admission = AdmissionPatient::factory()->create([
            'ADMISSION_CODE' => 800, 'ADMISSION_PATREC_CD' => 6001, 'ADMISSION_LOC_CD' => 11,
            'ADMISSION_ENTER_DOC_CD' => 4001, 'ADMISSION_ENTER_DATE' => now()->subDays(5),
        ]);
        InpatientStay::factory()->create(['INPATIENT_CODE' => 900, 'INPATIENT_ADMISSION_CD' => 800, 'INPATIENT_SUP_LOC_CD' => 10]);

        // A newer open admission of hospital 2 with the same patient code, and a cancelled-only admission here.
        AdmissionPatient::factory()->forHospital(self::OTHER)->create([
            'ADMISSION_CODE' => 801, 'ADMISSION_PATREC_CD' => 6001, 'ADMISSION_ENTER_DATE' => now()->subDay(),
        ]);
        InpatientStay::factory()->forHospital(self::OTHER)->create(['INPATIENT_CODE' => 901, 'INPATIENT_ADMISSION_CD' => 801, 'INPATIENT_SUP_LOC_CD' => 10]);
        AdmissionPatient::factory()->create(['ADMISSION_CODE' => 802, 'ADMISSION_PATREC_CD' => 6001, 'ADMISSION_ENTER_DATE' => now()->subHours(2)]);
        InpatientStay::factory()->cancelled()->create(['INPATIENT_CODE' => 902, 'INPATIENT_ADMISSION_CD' => 802]);

        $patient = MrPatient::query()->findOrFail(6001);

        $this->assertSame(800, $patient->currentAdmission->ADMISSION_CODE);
        $this->assertSame('الجراحة رجال', $patient->hospital_ward_name);
        $this->assertSame($doctor->full_name, $patient->currentAdmission->doctor->full_name);
        $this->assertTrue($patient->currentAdmission->is($admission));

        $eager = MrPatient::query()->with('currentAdmission.currentStay.ward')->findOrFail(6001);
        $this->assertSame(800, $eager->currentAdmission->ADMISSION_CODE);
        $this->assertSame('الجراحة رجال', $eager->hospital_ward_name);
    }

    public function test_sex_is_read_from_the_hospitals_own_lookup_and_never_guessed(): void
    {
        SexLookup::factory()->create(['SEX_CODE' => 1, 'SEX_NAME_AR' => 'انثى', 'SEX_NAME_EN' => 'Female']);
        SexLookup::factory()->forHospital(self::OTHER)->create(['SEX_CODE' => 1, 'SEX_NAME_AR' => 'ذكر', 'SEX_NAME_EN' => 'Male']);
        SexLookup::factory()->create(['SEX_CODE' => 3, 'SEX_NAME_AR' => 'غير محدد', 'SEX_NAME_EN' => 'male']);

        $female = MrPatient::factory()->create(['MRP_SEX_CD' => 1]);
        $oddName = MrPatient::factory()->create(['MRP_SEX_CD' => 3]);
        $noCode = MrPatient::factory()->create(['MRP_SEX_CD' => null]);
        $missingCode = MrPatient::factory()->create(['MRP_SEX_CD' => 9]);

        $this->assertSame('انثى', $female->sex_label);
        $this->assertSame(SexLookup::FEMALE, $female->sex_class);
        $this->assertSame(SexLookup::UNKNOWN, $oddName->sex_class);
        $this->assertSame(SexLookup::UNKNOWN, $noCode->sex_class);
        $this->assertNull($noCode->sex_label);
        $this->assertSame(SexLookup::UNKNOWN, $missingCode->sex_class);
    }

    public function test_validation_rules_accept_only_codes_of_the_configured_hospital(): void
    {
        Employee::factory()->create(['EMP_SERIAL' => 3101]);
        Employee::factory()->forHospital(self::OTHER)->create(['EMP_SERIAL' => 3102]);
        MrPatient::factory()->create(['MRP_PATREC_CODE' => 5101]);
        MrPatient::factory()->forHospital(self::OTHER)->create(['MRP_PATREC_CODE' => 5102]);

        $passes = fn (string $rule, mixed $value): bool => Validator::make(
            ['code' => $value],
            ['code' => [$rule === 'employee' ? new HospitalEmployeeExists : new HospitalPatientExists]]
        )->passes();

        $this->assertTrue($passes('employee', '3101'));
        $this->assertTrue($passes('employee', 3101));
        $this->assertFalse($passes('employee', '3102'));
        $this->assertFalse($passes('employee', '31O1'));
        $this->assertFalse($passes('employee', ['3101']));
        $this->assertTrue($passes('patient', '5101'));
        $this->assertFalse($passes('patient', '5102'));
        $this->assertFalse($passes('patient', "5101' OR 1=1"));
    }

    public function test_saving_a_link_to_another_hospitals_code_is_rejected_by_the_model(): void
    {
        $user = $this->admin();
        $patient = MrPatient::factory()->create(['MRP_PATREC_CODE' => 5201]);
        $nurse = Employee::factory()->create(['EMP_SERIAL' => 3201]);
        Employee::factory()->forHospital(self::OTHER)->create(['EMP_SERIAL' => 3202]);
        MrPatient::factory()->forHospital(self::OTHER)->create(['MRP_PATREC_CODE' => 5202]);

        $attributes = fn (array $overrides = []): array => [
            'WC_REQ_CODE' => (string) Str::uuid(), 'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name, 'WC_REQ_PAT_ID_SNAPSHOT' => (string) $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $nurse->EMP_SERIAL, 'WC_REQ_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING', 'WC_REQ_CREATED_AT' => now(), ...$overrides,
        ];

        $request = WcRequestReg::create($attributes());
        $this->assertSame(1, (int) $request->WC_REQ_HOS_NO);

        foreach ([
            ['WC_REQ_NURSE_CD' => 3202],
            ['WC_REQ_PATIENT_CD' => 5202],
            ['WC_REQ_REVIEWED_BY_CD' => 3202],
            ['WC_REQ_HOS_NO' => self::OTHER],
        ] as $overrides) {
            try {
                WcRequestReg::create($attributes($overrides));
                $this->fail('Saved a link outside hospital 1: '.json_encode($overrides));
            } catch (HospitalLinkException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->expectException(HospitalLinkException::class);
        $request->update(['WC_REQ_REVIEWED_BY_CD' => 3202]);
    }

    public function test_user_linked_to_another_hospitals_employee_has_no_employee_here(): void
    {
        Employee::factory()->named('موظف', null, null, 'محلي')->create(['EMP_SERIAL' => 3301]);
        $foreignUserCode = (string) Str::uuid();
        DB::table('wc_users_tb')->insert([
            'PER_USER_CODE' => $foreignUserCode, 'PER_USER_USERNAME' => 'from_hospital_2', 'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1, 'PER_USER_EMP_CD' => 3301, 'PER_USER_HOS_NO' => self::OTHER,
        ]);

        $user = User::query()->findOrFail($foreignUserCode);
        $this->assertNull($user->employee);
        $this->assertNull(User::query()->with('employee')->findOrFail($foreignUserCode)->employee);
        $this->assertNull(Employee::query()->findOrFail(3301)->user);
    }

    public function test_wccms_rows_of_another_hospital_are_invisible(): void
    {
        [, , $registration] = $this->ownRegistration();
        $foreign = $this->foreignRegistration();

        $this->assertSame([$registration->WC_REG_CODE], WcRegistration::query()->pluck('WC_REG_CODE')->all());
        $this->assertNull(WcRegistration::query()->find($foreign['registration']));
        $this->assertNull(WcRequestReg::query()->find($foreign['request']));
        $this->assertNull(WcDailyReview::query()->find($foreign['review']));
        $this->assertNull(WcScheduledReview::query()->find($foreign['schedule']));
        $this->assertSame(0, WcScheduledReview::query()->where('WC_SREV_REGISTRATION_CD', $foreign['registration'])->count());
    }

    public function test_dashboard_and_patient_history_report_only_the_configured_hospital(): void
    {
        [$patient] = $this->ownRegistration();
        $foreign = $this->foreignRegistration();
        $reports = app(ReportingService::class);

        $kpis = $reports->getKpis('all');
        $this->assertSame(1, $kpis['pending_requests']);
        $this->assertSame(0, $kpis['total_evaluations']);

        $this->assertSame([], $reports->getRecentActivities(10));
        $this->assertNull($reports->searchPatientHistory((string) $foreign['patient_code']));
        $this->assertNull($reports->searchPatientHistory('مستشفى آخر'));

        $history = $reports->searchPatientHistory((string) $patient->MRP_PATREC_CODE);
        $this->assertSame($patient->MRP_PATREC_CODE, $history['patient']['code']);
        $this->assertCount(1, $history['registrations']);
        $this->assertSame('غير معروف', $history['patient']['gender']);
    }

    public function test_monthly_report_and_export_cover_the_configured_hospital_only(): void
    {
        [$patient, , $registration] = $this->ownRegistration();
        $this->foreignRegistration();

        $month = CarbonImmutable::now()->startOfMonth();
        $cases = app(MonthlyCaseReportService::class)->cases($month, $month);

        $this->assertSame([$registration->WC_REG_CODE], $cases->pluck('registrationCode')->all());
        $this->assertSame($patient->full_name, $cases->first()->patientName);
        $this->assertSame('Unknown', $cases->first()->gender);

        $summary = app(MonthlyCaseReportService::class)->summary($month, $month);
        $this->assertSame(1, $summary['totalCases']);
        $this->assertContains(['label' => 'Unknown', 'count' => 1], $summary['perGender']);
    }

    /**
     * @return array{0: MrPatient, 1: Employee, 2: WcRegistration}
     */
    private function ownRegistration(): array
    {
        $user = $this->admin();
        $patient = MrPatient::factory()->named('مريض', 'المستشفى', null, 'الأول')->create();
        $nurse = Employee::factory()->named('ممرض', 'العيادة', null, 'الأول')->create();

        $request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(), 'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name, 'WC_REQ_PAT_ID_SNAPSHOT' => (string) $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $nurse->EMP_SERIAL, 'WC_REQ_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING', 'WC_REQ_CREATED_AT' => now(),
        ]);
        $registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(), 'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE, 'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $nurse->EMP_SERIAL, 'WC_REG_ENTRY_USER_CD' => $user->PER_USER_CODE, 'WC_REG_STATUS' => 'ACTIVE',
        ]);

        return [$patient, $nurse, $registration];
    }

    /**
     * Rows another WCCMS deployment (hospital 2) wrote to the same tables, inserted around the models' guards.
     *
     * @return array{patient_code: int, request: string, registration: string, review: string, schedule: string}
     */
    private function foreignRegistration(): array
    {
        $patient = MrPatient::factory()->forHospital(self::OTHER)->named('مريض', 'مستشفى', null, 'آخر')->create();
        $nurse = Employee::factory()->forHospital(self::OTHER)->create();
        $userCode = (string) Str::uuid();
        DB::table('wc_users_tb')->insert(['PER_USER_CODE' => $userCode, 'PER_USER_USERNAME' => 'h2_'.Str::random(6), 'PER_USER_ROLE' => 'NURSE', 'PER_USER_IS_ACTIVE' => 1, 'PER_USER_HOS_NO' => self::OTHER]);

        $ids = ['patient_code' => $patient->MRP_PATREC_CODE, 'request' => (string) Str::uuid(), 'registration' => (string) Str::uuid(), 'review' => (string) Str::uuid(), 'schedule' => (string) Str::uuid()];

        DB::table('wc_request_reg_tb')->insert([
            'WC_REQ_CODE' => $ids['request'], 'WC_REQ_HOS_NO' => self::OTHER, 'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name, 'WC_REQ_PAT_ID_SNAPSHOT' => (string) $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $nurse->EMP_SERIAL, 'WC_REQ_ENTRY_USER_CD' => $userCode, 'WC_REQ_STATUS' => 'PENDING', 'WC_REQ_CREATED_AT' => now(),
        ]);
        DB::table('wc_registration_tb')->insert([
            'WC_REG_CODE' => $ids['registration'], 'WC_REG_HOS_NO' => self::OTHER, 'WC_REG_REQUEST_CD' => $ids['request'],
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE, 'WC_REG_DATE' => now(), 'WC_REG_NURSE_CD' => $nurse->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $userCode, 'WC_REG_STATUS' => 'ACTIVE',
        ]);
        DB::table('wc_scheduled_review_tb')->insert([
            'WC_SREV_CODE' => $ids['schedule'], 'WC_SREV_REGISTRATION_CD' => $ids['registration'], 'WC_SREV_CREATED_BY_CD' => $userCode,
            'WC_SREV_SCHEDULED_DATE' => now()->subDay(), 'WC_SREV_VISIT_SERIAL' => 1, 'WC_SREV_STATUS' => 'MISSED', 'WC_SREV_CREATED_ON' => now(),
        ]);
        DB::table('wc_daily_review_tb')->insert([
            'WC_DREV_CODE' => $ids['review'], 'WC_DREV_HOS_NO' => self::OTHER, 'WC_DREV_REGISTRATION_CD' => $ids['registration'],
            'WC_DREV_VISIT_DATE' => now(), 'WC_DREV_NURSE_CD' => $nurse->EMP_SERIAL, 'WC_DREV_ENTRY_USER_CD' => $userCode,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_STAY',
        ]);

        return $ids;
    }

    private function admin(): User
    {
        return User::create([
            'PER_USER_CODE' => (string) Str::uuid(), 'PER_USER_USERNAME' => 'iso_'.Str::random(8),
            'PER_USER_ROLE' => 'ADMIN', 'PER_USER_IS_ACTIVE' => 1,
        ]);
    }
}
