<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Models\WcWoundPhoto;
use App\Models\WcWoundType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PatientMedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_patient_record_orders_all_registrations_and_opens_newest_active(): void
    {
        $context = $this->createClinicalContext();
        $activeCode = '00000000-0000-0000-0000-000000000100';
        $newerHistoricalCode = '00000000-0000-0000-0000-000000000300';
        $olderHistoricalCode = '00000000-0000-0000-0000-000000000200';

        $this->createRegistration($context, '2026-01-01 09:00:00', 'ACTIVE', $activeCode);
        $this->createRegistration($context, '2025-12-01 09:00:00', 'DISCHARGED', $olderHistoricalCode);
        $this->createRegistration($context, '2025-12-01 09:00:00', 'DISCHARGED', $newerHistoricalCode);

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSeeInOrder([$activeCode, $newerHistoricalCode, $olderHistoricalCode]);
        $response->assertSee('data-registration-code="'.$activeCode.'"', false);
        $response->assertSee('data-initially-open="true"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'id="patient-demographics-name">'.$context['patient']->full_name));
    }

    public function test_patient_record_opens_newest_historical_registration_when_none_is_active(): void
    {
        $context = $this->createClinicalContext();
        $newestCode = '00000000-0000-0000-0000-000000000402';
        $oldestCode = '00000000-0000-0000-0000-000000000401';

        $this->createRegistration($context, '2026-02-01 09:00:00', 'DISCHARGED', $oldestCode);
        $this->createRegistration($context, '2026-03-01 09:00:00', 'DISCHARGED', $newestCode);

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSeeInOrder([$newestCode, $oldestCode]);
        $response->assertSee('data-registration-code="'.$newestCode.'"', false);
        $response->assertSee('data-initially-open="true"', false);
    }

    public function test_single_registration_renders_without_a_separate_empty_history_area(): void
    {
        $context = $this->createClinicalContext();
        $registration = $this->createRegistration($context, '2026-04-01 09:00:00');

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSee($registration->WC_REG_CODE);
        $response->assertDontSee('لا توجد تسجيلات سابقة');
        $this->assertSame(1, substr_count($response->getContent(), 'data-registration-code='));
    }

    public function test_patient_without_registrations_keeps_identity_and_shows_clear_empty_state(): void
    {
        $context = $this->createClinicalContext();

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSee($context['patient']->full_name);
        $response->assertSee('لا توجد تسجيلات سريرية لهذا المريض');
        $response->assertDontSee('data-registration-code=', false);
    }

    public function test_registration_clinical_events_are_isolated_and_photos_render_once(): void
    {
        $context = $this->createClinicalContext();
        $current = $this->createRegistration(
            $context,
            '2026-05-10 09:00:00',
            'ACTIVE',
            '00000000-0000-0000-0000-000000000510',
        );
        $historical = $this->createRegistration(
            $context,
            '2025-05-10 09:00:00',
            'DISCHARGED',
            '00000000-0000-0000-0000-000000000410',
        );
        $currentWound = $this->createWound($context, $current, 'جرح التسجيل الحالي', 'وصف الجرح الحالي');
        $historicalWound = $this->createWound($context, $historical, 'جرح التسجيل السابق', 'وصف الجرح السابق');
        $currentReview = $this->createReview(
            $context,
            $current,
            $currentWound,
            '2026-05-11 10:00:00',
            'ملاحظة مراجعة التسجيل الحالي',
        );
        $historicalReview = $this->createReview(
            $context,
            $historical,
            $historicalWound,
            '2025-05-11 10:00:00',
            'ملاحظة مراجعة التسجيل السابق',
        );
        $baselinePhoto = $this->createPhoto($context, $currentWound, null, 'INITIAL', 'baseline-current.jpg');
        $reviewPhoto = $this->createPhoto(
            $context,
            $currentWound,
            $currentReview['detail'],
            'REVIEW',
            'review-current.jpg',
        );

        $otherContext = $this->createClinicalContext();
        $otherRegistration = $this->createRegistration($otherContext, '2026-05-12 09:00:00');
        $otherWound = $this->createWound($otherContext, $otherRegistration, 'جرح مريض آخر', 'يجب ألا يظهر');

        $response = $this->getPatientRecord($context);
        $html = $response->getContent();
        $currentFragment = $this->registrationFragment($html, $current->WC_REG_CODE, $historical->WC_REG_CODE);
        $historicalFragment = $this->registrationFragment($html, $historical->WC_REG_CODE);

        $response->assertOk();
        $this->assertStringContainsString('جرح التسجيل الحالي', $currentFragment);
        $this->assertStringContainsString('ملاحظة مراجعة التسجيل الحالي', $currentFragment);
        $this->assertStringNotContainsString('جرح التسجيل السابق', $currentFragment);
        $this->assertStringContainsString('جرح التسجيل السابق', $historicalFragment);
        $this->assertStringContainsString('ملاحظة مراجعة التسجيل السابق', $historicalFragment);
        $this->assertStringNotContainsString('جرح التسجيل الحالي', $historicalFragment);
        $response->assertDontSee($otherWound->WC_RDIAG_DESCRIPTION);
        $this->assertSame(1, substr_count($html, 'data-photo-code="'.$baselinePhoto->WC_PHOTO_CODE.'"'));
        $this->assertSame(1, substr_count($html, 'data-photo-code="'.$reviewPhoto->WC_PHOTO_CODE.'"'));
        $this->assertStringContainsString(
            'href="#baseline-'.$current->WC_REG_CODE.'-'.$currentWound->WC_RDIAG_CODE.'"',
            $currentFragment,
        );
    }

    public function test_reviews_and_appointments_are_deterministic_and_visually_distinct(): void
    {
        $context = $this->createClinicalContext();
        $registration = $this->createRegistration($context, '2026-06-01 09:00:00');
        $wound = $this->createWound($context, $registration, 'جرح ترتيب المراجعات', 'وصف ثابت');
        $scheduled = $this->createAppointment(
            $context,
            $registration,
            '2026-06-10 10:00:00',
            'PENDING',
            '00000000-0000-0000-0000-000000000610',
        );

        $this->createReview(
            $context,
            $registration,
            $wound,
            '2026-06-10 10:00:00',
            'المراجعة الأقدم عند تساوي التاريخ',
            '00000000-0000-0000-0000-000000000611',
        );
        $this->createReview(
            $context,
            $registration,
            $wound,
            '2026-06-10 10:00:00',
            'المراجعة الأحدث عند تساوي التاريخ',
            '00000000-0000-0000-0000-000000000612',
            $scheduled,
        );

        $this->createAppointment($context, $registration, '2026-06-09 10:00:00', 'COMPLETED');
        $this->createAppointment($context, $registration, '2026-06-08 10:00:00', 'CANCELLED');
        $this->createAppointment($context, $registration, '2026-06-07 10:00:00', 'MISSED');
        $this->createAppointment($context, $registration, '2026-06-06 10:00:00', 'VOIDED');

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSeeInOrder([
            'المراجعة الأحدث عند تساوي التاريخ',
            'المراجعة الأقدم عند تساوي التاريخ',
        ]);
        $response->assertSee('مراجعة مجدولة');
        $response->assertSee('مراجعة يومية');
        $response->assertSee('قيد الانتظار');
        $response->assertSee('مكتمل');
        $response->assertSee('ملغى');
        $response->assertSee('لم يحضر');
        $response->assertSee('ملغى تلقائياً');
        $response->assertSee('ضماد اختبار');
        $response->assertSee('علاج اختبار');
        $response->assertSee('Improved – Follow-up');
    }

    public function test_empty_registration_sections_have_independent_empty_states(): void
    {
        $context = $this->createClinicalContext();
        $this->createRegistration($context, '2026-07-01 09:00:00');

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSee('لا توجد جروح مسجلة');
        $response->assertSee('لا توجد مواعيد مجدولة');
        $response->assertSee('لا توجد مراجعات مسجلة');
    }

    public function test_each_registration_links_to_its_dedicated_record_and_aggregate_is_read_only(): void
    {
        $context = $this->createClinicalContext();
        $active = $this->createRegistration($context, '2026-08-02 09:00:00');
        $historical = $this->createRegistration($context, '2026-08-01 09:00:00', 'DISCHARGED');

        $response = $this->getPatientRecord($context);

        $response->assertOk();
        $response->assertSee(route('registrations.show', $active->WC_REG_CODE), false);
        $response->assertSee(route('registrations.show', $historical->WC_REG_CODE), false);
        $response->assertSee('فتح الملف التفصيلي', false);
        $response->assertDontSee('إدخال مراجعة يومية');
        $response->assertDontSee('إضافة تشخيص جرح');
    }

    public function test_discharged_registration_page_returns_to_history_and_keeps_mutations_hidden(): void
    {
        $context = $this->createClinicalContext();
        $historical = $this->createRegistration($context, '2026-08-01 09:00:00', 'DISCHARGED');

        $response = $this->get(route('registrations.show', $historical->WC_REG_CODE));

        $response->assertOk();
        $response->assertSee(route('patients.show', $context['patient']->MRP_PATREC_CODE), false);
        $response->assertSee('كل تسجيلات المريض');
        $response->assertDontSee('إدخال مراجعة يومية');
        $response->assertDontSee('إضافة تشخيص جرح');
    }

    public function test_representative_longitudinal_record_has_bounded_queries_unique_ids_and_lazy_photos(): void
    {
        $smallContext = $this->createClinicalContext();
        $smallRegistration = $this->createRegistration($smallContext, '2026-10-01 09:00:00');
        $smallWound = $this->createWound(
            $smallContext,
            $smallRegistration,
            'تشخيص العينة الصغيرة',
            'جرح العينة الصغيرة',
        );
        $smallReview = $this->createReview(
            $smallContext,
            $smallRegistration,
            $smallWound,
            '2026-10-01 10:00:00',
            'مراجعة العينة الصغيرة',
        );
        $this->createPhoto($smallContext, $smallWound, null, 'INITIAL', 'small-baseline.jpg');
        $this->createPhoto(
            $smallContext,
            $smallWound,
            $smallReview['detail'],
            'REVIEW',
            'small-review.jpg',
        );

        DB::enableQueryLog();
        DB::flushQueryLog();
        $smallResponse = $this->getPatientRecord($smallContext);
        $smallQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $smallResponse->assertOk();

        $largeContext = $this->createClinicalContext();
        $registrations = [];

        for ($registrationIndex = 1; $registrationIndex <= 10; $registrationIndex++) {
            $registration = $this->createRegistration(
                $largeContext,
                sprintf('2026-10-%02d 09:00:00', $registrationIndex),
            );
            $registrations[] = $registration;

            for ($reviewIndex = 1; $reviewIndex <= 20; $reviewIndex++) {
                $this->createBareReview(
                    $largeContext,
                    $registration,
                    sprintf('2026-10-%02d %02d:00:00', $registrationIndex, ($reviewIndex % 20) + 1),
                );
            }
        }

        $wound = $this->createWound(
            $largeContext,
            $registrations[0],
            'تشخيص سجل الأداء',
            'جرح لاختبار الصور الكسولة',
        );
        $review = WcDailyReview::query()
            ->where('WC_DREV_REGISTRATION_CD', $registrations[0]->WC_REG_CODE)
            ->firstOrFail();
        $detail = WcDailyReviewDetail::create([
            'WC_REVD_CODE' => (string) Str::uuid(),
            'WC_REVD_REVIEW_CD' => $review->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
            'WC_DREVD_FOLLOW_UP_REQUIRED' => false,
        ]);
        $baselinePhoto = $this->createPhoto($largeContext, $wound, null, 'INITIAL', 'performance-baseline.jpg');
        $reviewPhoto = $this->createPhoto($largeContext, $wound, $detail, 'REVIEW', 'performance-review.jpg');

        DB::enableQueryLog();
        DB::flushQueryLog();
        $startedAt = microtime(true);
        $largeResponse = $this->getPatientRecord($largeContext);
        $elapsedSeconds = microtime(true) - $startedAt;
        $largeQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $largeResponse->assertOk();
        $html = $largeResponse->getContent();
        preg_match_all('/\sid="([^"]+)"/', $html, $idMatches);

        $this->assertSame(10, substr_count($html, 'data-registration-code='));
        $this->assertSame(200, substr_count($html, 'data-review-code='));
        $this->assertSame(1, substr_count($html, 'data-photo-code="'.$baselinePhoto->WC_PHOTO_CODE.'"'));
        $this->assertSame(1, substr_count($html, 'data-photo-code="'.$reviewPhoto->WC_PHOTO_CODE.'"'));
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'loading="lazy"'));
        $this->assertSame(count($idMatches[1]), count(array_unique($idMatches[1])));
        $this->assertLessThanOrEqual($smallQueryCount + 2, $largeQueryCount);
        $this->assertLessThan(2.0, $elapsedSeconds);
    }

    public function test_audit_identities_remain_separate_and_scoped_to_each_registration(): void
    {
        $firstContext = $this->createClinicalContext();
        $this->renameEmployee($firstContext['employee'], 'الممرض السريري الأول');
        $firstRecorder = $this->createEntryUser('first_record_entry');
        $firstRegistration = $this->createRegistration(
            $firstContext,
            '2026-09-02 09:00:00',
            'ACTIVE',
            '00000000-0000-0000-0000-000000000902',
        );
        $firstRegistration->update(['WC_REG_ENTRY_USER_CD' => $firstRecorder->PER_USER_CODE]);
        $firstWound = $this->createWound($firstContext, $firstRegistration, 'تشخيص الحدث الأول', 'وصف الحدث الأول');
        $firstWound->update(['WC_RDIAG_ENTRY_USER_CD' => $firstRecorder->PER_USER_CODE]);
        $firstReview = $this->createReview(
            $firstContext,
            $firstRegistration,
            $firstWound,
            '2026-09-03 10:00:00',
            'مراجعة الحدث الأول',
        );
        $firstReview['review']->update(['WC_DREV_ENTRY_USER_CD' => $firstRecorder->PER_USER_CODE]);
        $firstBaselinePhoto = $this->createPhoto($firstContext, $firstWound, null, 'INITIAL', 'audit-baseline.jpg');
        $firstBaselinePhoto->update(['WC_PHOTO_ENTRY_USER_CD' => $firstRecorder->PER_USER_CODE]);
        $firstReviewPhoto = $this->createPhoto(
            $firstContext,
            $firstWound,
            $firstReview['detail'],
            'REVIEW',
            'audit-review.jpg',
        );
        $firstReviewPhoto->update(['WC_PHOTO_ENTRY_USER_CD' => $firstRecorder->PER_USER_CODE]);

        $secondContext = $this->createClinicalContext();
        $secondContext['patient'] = $firstContext['patient'];
        $this->renameEmployee($secondContext['employee'], 'الممرض السريري الثاني');
        $secondRecorder = $this->createEntryUser('second_record_entry');
        $secondContext['user'] = $secondRecorder;
        $secondRegistration = $this->createRegistration(
            $secondContext,
            '2026-09-01 09:00:00',
            'DISCHARGED',
            '00000000-0000-0000-0000-000000000901',
        );
        $secondWound = $this->createWound($secondContext, $secondRegistration, 'تشخيص الحدث الثاني', 'وصف الحدث الثاني');

        $response = $this->getPatientRecord($firstContext);
        $html = $response->getContent();
        $firstFragment = $this->registrationFragment(
            $html,
            $firstRegistration->WC_REG_CODE,
            $secondRegistration->WC_REG_CODE,
        );
        $secondFragment = $this->registrationFragment($html, $secondRegistration->WC_REG_CODE);

        $response->assertOk();
        $this->assertStringContainsString('مدخل بيانات التسجيل', $firstFragment);
        $this->assertStringContainsString('الموظف السريري', $firstFragment);
        $this->assertStringContainsString('مدخل البيانات', $firstFragment);
        $this->assertStringContainsString('التقاط سريري', $firstFragment);
        $this->assertStringContainsString('إدخال الصورة', $firstFragment);
        $this->assertStringContainsString('الممرض السريري الأول', $firstFragment);
        $this->assertStringContainsString('first_record_entry', $firstFragment);
        $this->assertStringNotContainsString('الممرض السريري الثاني', $firstFragment);
        $this->assertStringNotContainsString('second_record_entry', $firstFragment);
        $this->assertStringContainsString('منفذ التخريج', $secondFragment);
        $this->assertStringContainsString('الممرض السريري الثاني', $secondFragment);
        $this->assertStringContainsString('second_record_entry', $secondFragment);
        $this->assertStringNotContainsString('first_record_entry', $secondFragment);
        $this->assertStringContainsString($secondWound->WC_RDIAG_DESCRIPTION, $secondFragment);
    }

    private function createEntryUser(string $username): User
    {
        return User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => $username,
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);
    }

    /**
     * @return array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}
     */
    private function createClinicalContext(): array
    {
        $user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'history_nurse_'.Str::lower(Str::random(6)),
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $employee = Employee::factory()->named('ممرض السجل الطولي')->create();

        $user->update(['PER_USER_EMP_CD' => $employee->EMP_SERIAL]);

        $department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'عيادة العناية بالجروح',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        return [
            'user' => $user,
            'employee' => $employee,
            'department' => $department,
            'patient' => $this->createPatient(),
        ];
    }

    private function createPatient(): MrPatient
    {
        return MrPatient::factory()->male()->named('مريض السجل الطبي')->create([
            'MRP_MOBILE_NO' => '0599000000',
            'MRP_DOB' => '1980-01-01',
        ]);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function createWound(
        array $context,
        WcRegistration $registration,
        string $diagnosisName,
        string $description,
    ): WcRegDiagnosis {
        $woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'نوع '.$diagnosisName,
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);
        $diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => $diagnosisName,
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        return WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DESCRIPTION' => $description,
            'WC_RDIAG_CLINICAL_EMP_CD' => $context['employee']->EMP_SERIAL,
            'WC_RDIAG_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
        ]);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function createBareReview(
        array $context,
        WcRegistration $registration,
        string $visitDate,
    ): WcDailyReview {
        return WcDailyReview::create([
            'WC_DREV_CODE' => (string) Str::uuid(),
            'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_DREV_VISIT_DATE' => $visitDate,
            'WC_DREV_NURSE_CD' => $context['employee']->EMP_SERIAL,
            'WC_DREV_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_STAY',
            'WC_DREV_GENERAL_NOTES' => 'مراجعة سجل الأداء',
        ]);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     * @return array{review: WcDailyReview, detail: WcDailyReviewDetail}
     */
    private function createReview(
        array $context,
        WcRegistration $registration,
        WcRegDiagnosis $wound,
        string $visitDate,
        string $notes,
        ?string $reviewCode = null,
        ?WcScheduledReview $scheduledReview = null,
    ): array {
        $dressing = WcDressing::create([
            'WC_DRESS_CODE' => (string) Str::uuid(),
            'WC_DRESS_NAME' => 'ضماد اختبار',
            'WC_DRESS_IS_ACTIVE' => 1,
        ]);
        $medication = WcMedication::create([
            'WC_MED_CODE' => (string) Str::uuid(),
            'WC_MED_NAME' => 'علاج اختبار',
            'WC_MED_IS_ACTIVE' => 1,
        ]);
        $review = WcDailyReview::create([
            'WC_DREV_CODE' => $reviewCode ?? (string) Str::uuid(),
            'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduledReview?->WC_SREV_CODE,
            'WC_DREV_VISIT_DATE' => $visitDate,
            'WC_DREV_NURSE_CD' => $context['employee']->EMP_SERIAL,
            'WC_DREV_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'IMPROVED_STAY',
            'WC_DREV_GENERAL_NOTES' => $notes,
        ]);
        $detail = WcDailyReviewDetail::create([
            'WC_REVD_CODE' => (string) Str::uuid(),
            'WC_REVD_REVIEW_CD' => $review->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_DREVD_WOUND_DESCRIPTION' => 'ملاحظة سريرية للجرح',
            'WC_DREVD_WOUND_CHANGES' => 'تحسن ملحوظ',
            'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
            'WC_DREVD_MEDICATION_CD' => $medication->WC_MED_CODE,
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
            'WC_DREVD_FOLLOW_UP_REQUIRED' => true,
        ]);

        return compact('review', 'detail');
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function createAppointment(
        array $context,
        WcRegistration $registration,
        string $scheduledDate,
        string $status,
        ?string $appointmentCode = null,
    ): WcScheduledReview {
        return WcScheduledReview::create([
            'WC_SREV_CODE' => $appointmentCode ?? (string) Str::uuid(),
            'WC_SREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_SREV_CREATED_BY_CD' => $context['user']->PER_USER_CODE,
            'WC_SREV_SCHEDULED_DATE' => $scheduledDate,
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => $status,
            'WC_SREV_NOTES' => 'ملاحظة موعد '.$status,
            'WC_SREV_CREATED_ON' => $scheduledDate,
            'WC_SREV_COMPLETED_ON' => $status === 'COMPLETED' ? $scheduledDate : null,
        ]);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function createPhoto(
        array $context,
        WcRegDiagnosis $wound,
        ?WcDailyReviewDetail $detail,
        string $stage,
        string $path,
    ): WcWoundPhoto {
        return WcWoundPhoto::create([
            'WC_PHOTO_CODE' => (string) Str::uuid(),
            'WC_PHOTO_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
            'WC_PHOTO_REVIEW_DETAIL_CD' => $detail?->WC_REVD_CODE,
            'WC_PHOTO_STAGE' => $stage,
            'WC_PHOTO_PATH' => $path,
            'WC_PHOTO_CAPTION' => 'صورة '.$stage,
            'WC_PHOTO_TAKEN_AT' => '2026-05-11 10:30:00',
            'WC_PHOTO_NURSE_CD' => $context['employee']->EMP_SERIAL,
            'WC_PHOTO_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
        ]);
    }

    private function registrationFragment(
        string $html,
        string $registrationCode,
        ?string $nextRegistrationCode = null,
    ): string {
        $start = strpos($html, 'data-registration-code="'.$registrationCode.'"');
        $this->assertNotFalse($start);

        if ($nextRegistrationCode === null) {
            return substr($html, $start);
        }

        $end = strpos($html, 'data-registration-code="'.$nextRegistrationCode.'"', $start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function createRegistration(
        array $context,
        string $registrationDate,
        string $status = 'ACTIVE',
        ?string $registrationCode = null,
    ): WcRegistration {
        $request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $context['patient']->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $context['patient']->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $context['patient']->MRP_ID,
            'WC_REQ_NURSE_CD' => $context['employee']->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => $registrationDate,
        ]);

        return WcRegistration::create([
            'WC_REG_CODE' => $registrationCode ?? (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $context['patient']->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $context['department']->WC_DEPT_CODE,
            'WC_REG_DATE' => $registrationDate,
            'WC_REG_NURSE_CD' => $context['employee']->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $context['user']->PER_USER_CODE,
            'WC_REG_STATUS' => $status,
            'WC_REG_DISCHARGED_AT' => $status === 'DISCHARGED' ? $registrationDate : null,
            'WC_REG_DISCHARGED_BY_EMP_CD' => $status === 'DISCHARGED'
                ? $context['employee']->EMP_SERIAL
                : null,
        ]);
    }

    /**
     * @param  array{user: User, employee: Employee, department: WcDepartment, patient: MrPatient}  $context
     */
    private function getPatientRecord(array $context): TestResponse
    {
        return $this->get(route('patients.show', $context['patient']->MRP_PATREC_CODE));
    }

    /**
     * EMPLOYEE_TB is read only through its model; the test edits the local copy directly.
     */
    private function renameEmployee(Employee $employee, string $firstName): void
    {
        DB::table($employee->getTable())
            ->where(['EMP_SERIAL' => $employee->EMP_SERIAL, 'HOS_NO' => $employee->HOS_NO])
            ->update(['EMP_FIRST_NAME_AR' => $firstName]);
    }
}
