<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\PerUser;
use App\Models\SexLookup;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Models\WcWoundPhoto;
use App\Models\WcWoundType;
use App\Support\Hospital;
use App\Support\LocalHospitalCopy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * One demo patient with a long wound history. The patient and the nurse are rows of the LOCAL copies of
 * MR_PATIENT_TB and EMPLOYEE_TB in the configured hospital (HOSPITAL_NO); with HOSPITAL_SCHEMA set those tables are
 * the real hospital ones, which WCCMS never writes to, so the seeder does nothing.
 */
class LongitudinalPatientSeeder extends Seeder
{
    /** MR_PATIENT_TB.MRP_PATREC_CODE (medical record number) of the demo patient. */
    public const PATIENT_CODE = 900001;

    /** MR_PATIENT_TB.MRP_ID (document number) of the demo patient. */
    public const PATIENT_ID = 400900001;

    /** EMPLOYEE_TB.EMP_SERIAL of the demo nurse. */
    public const EMPLOYEE_SERIAL = 9101;

    public function run(): void
    {
        if (Hospital::isExternal()) {
            $this->command?->warn('LongitudinalPatientSeeder skipped: HOSPITAL_SCHEMA points at the real hospital tables, which WCCMS never writes to.');

            return;
        }

        $this->call([
            WcDepartmentSeeder::class,
            WcDiagnosisSeeder::class,
            WcWoundTypeSeeder::class,
            WcDressingSeeder::class,
            WcMedicationSeeder::class,
            WcProcedureSeeder::class,
        ]);

        $patient = DB::transaction(function (): MrPatient {
            $department = WcDepartment::query()->where('WC_DEPT_IS_ACTIVE', 1)->firstOrFail();
            $procedure = WcProcedure::query()->where('WC_PROC_IS_ACTIVE', 1)->firstOrFail();
            $currentDiagnosis = WcDiagnosis::query()->where('WC_DIAG_IS_ACTIVE', 1)->firstOrFail();
            $historicalDiagnosis = WcDiagnosis::query()
                ->where('WC_DIAG_IS_ACTIVE', 1)
                ->whereKeyNot($currentDiagnosis->WC_DIAG_CODE)
                ->first() ?? $currentDiagnosis;
            $currentWoundType = WcWoundType::query()->where('WC_WTYPE_IS_ACTIVE', 1)->firstOrFail();
            $historicalWoundType = WcWoundType::query()
                ->where('WC_WTYPE_IS_ACTIVE', 1)
                ->whereKeyNot($currentWoundType->WC_WTYPE_CODE)
                ->first() ?? $currentWoundType;
            $dressing = WcDressing::query()->where('WC_DRESS_IS_ACTIVE', 1)->firstOrFail();
            $medication = WcMedication::query()->where('WC_MED_IS_ACTIVE', 1)->firstOrFail();

            $entryUser = PerUser::query()->firstOrCreate(
                ['PER_USER_USERNAME' => 'longitudinal_demo_nurse'],
                [
                    'PER_USER_CODE' => '11111111-1111-4111-8111-111111111111',
                    'PER_USER_ROLE' => 'NURSE',
                    'PER_USER_IS_ACTIVE' => 1,
                ],
            );
            $entryUser->update([
                'PER_USER_ROLE' => 'NURSE',
                'PER_USER_IS_ACTIVE' => 1,
            ]);

            // Hospital rows are only created when missing, never updated (they are the hospital's data).
            $clinicalEmployee = $entryUser->employee ?? LocalHospitalCopy::firstOrInsert(
                Employee::class,
                ['EMP_SERIAL' => self::EMPLOYEE_SERIAL],
                [
                    'EMP_FIRST_NAME_AR' => 'الممرضة التجريبية',
                    'EMP_LAST_NAME_AR' => 'للسجل الطولي',
                    'EMP_ACTIVE' => 1,
                    'EMP_REC_CD' => 1,
                ],
            );
            $entryUser->update(['PER_USER_EMP_CD' => $clinicalEmployee->EMP_SERIAL, 'PER_USER_HOS_NO' => Hospital::number()]);

            // Demo code only: WCCMS never assumes which code is male; reports classify by SEX_NAME_EN.
            $male = SexLookup::query()->where('SEX_NAME_EN', config('hospital.sex.male_name_en'))->first()
                ?? LocalHospitalCopy::firstOrInsert(SexLookup::class, ['SEX_CODE' => 2], ['SEX_NAME_AR' => 'ذكر', 'SEX_NAME_EN' => config('hospital.sex.male_name_en')]);

            $patient = LocalHospitalCopy::firstOrInsert(
                MrPatient::class,
                ['MRP_PATREC_CODE' => self::PATIENT_CODE],
                [
                    'MRP_ID_TYPE_CD' => 1,
                    'MRP_ID' => self::PATIENT_ID,
                    'MRP_FIRST_NAME_AR' => 'مريض تجريبي',
                    'MRP_LAST_NAME_AR' => 'سجل طولي',
                    'MRP_MOBILE_NO' => '0599001100',
                    'MRP_SEX_CD' => $male->SEX_CODE,
                    'MRP_DOB' => '1978-04-15',
                ],
            );

            $currentOpenedAt = now()->subDays(24)->setTime(9, 0);
            $historicalOpenedAt = now()->subDays(220)->setTime(9, 0);
            $historicalDischargedAt = now()->subDays(160)->setTime(14, 30);

            $currentRequest = WcRequestReg::query()->updateOrCreate(
                ['WC_REQ_CODE' => '11444444-4444-4444-8444-444444444441'],
                [
                    'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
                    'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
                    'WC_REQ_PAT_ID_SNAPSHOT' => (string) $patient->MRP_ID,
                    'WC_REQ_PAT_PHONE_SNAPSHOT' => $patient->MRP_MOBILE_NO,
                    'WC_REQ_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REQ_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_REQ_REVIEWED_BY_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REQ_STATUS' => 'ACCEPTED',
                    'WC_REQ_NOTES' => 'طلب التسجيل الحالي لاختبار السجل الطبي الطولي.',
                    'WC_REQ_CREATED_AT' => $currentOpenedAt->copy()->subDay(),
                    'WC_REQ_REVIEWED_ON' => $currentOpenedAt,
                ],
            );

            $historicalRequest = WcRequestReg::query()->updateOrCreate(
                ['WC_REQ_CODE' => '11444444-4444-4444-8444-444444444442'],
                [
                    'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
                    'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
                    'WC_REQ_PAT_ID_SNAPSHOT' => (string) $patient->MRP_ID,
                    'WC_REQ_PAT_PHONE_SNAPSHOT' => $patient->MRP_MOBILE_NO,
                    'WC_REQ_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REQ_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_REQ_REVIEWED_BY_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REQ_STATUS' => 'ACCEPTED',
                    'WC_REQ_NOTES' => 'طلب تسجيل سابق مكتمل لاختبار التاريخ الطبي.',
                    'WC_REQ_CREATED_AT' => $historicalOpenedAt->copy()->subDay(),
                    'WC_REQ_REVIEWED_ON' => $historicalOpenedAt,
                ],
            );

            $currentRegistration = WcRegistration::query()->updateOrCreate(
                ['WC_REG_CODE' => '11555555-5555-4555-8555-555555555551'],
                [
                    'WC_REG_REQUEST_CD' => $currentRequest->WC_REQ_CODE,
                    'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
                    'WC_REG_DEPARTMENT_CD' => $department->WC_DEPT_CODE,
                    'WC_REG_PROCEDURE_CD' => $procedure->WC_PROC_CODE,
                    'WC_REG_PMH_NOTES' => 'سكري من النوع الثاني وضغط دم تحت السيطرة.',
                    'WC_REG_DATE' => $currentOpenedAt,
                    'WC_REG_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_REG_STATUS' => 'ACTIVE',
                    'WC_REG_DISCHARGED_AT' => null,
                    'WC_REG_DISCHARGED_BY_EMP_CD' => null,
                ],
            );

            $historicalRegistration = WcRegistration::query()->updateOrCreate(
                ['WC_REG_CODE' => '11555555-5555-4555-8555-555555555552'],
                [
                    'WC_REG_REQUEST_CD' => $historicalRequest->WC_REQ_CODE,
                    'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
                    'WC_REG_DEPARTMENT_CD' => $department->WC_DEPT_CODE,
                    'WC_REG_PROCEDURE_CD' => $procedure->WC_PROC_CODE,
                    'WC_REG_PMH_NOTES' => 'تسجيل سابق لعلاج جرح جراحي حتى اكتمال الالتئام.',
                    'WC_REG_DATE' => $historicalOpenedAt,
                    'WC_REG_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_REG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_REG_STATUS' => 'DISCHARGED',
                    'WC_REG_DISCHARGED_AT' => $historicalDischargedAt,
                    'WC_REG_DISCHARGED_BY_EMP_CD' => $clinicalEmployee->EMP_SERIAL,
                ],
            );

            $currentWound = WcRegDiagnosis::query()->updateOrCreate(
                ['WC_RDIAG_CODE' => '11666666-6666-4666-8666-666666666661'],
                [
                    'WC_RDIAG_REGISTRATION_CD' => $currentRegistration->WC_REG_CODE,
                    'WC_RDIAG_DIAGNOSIS_CD' => $currentDiagnosis->WC_DIAG_CODE,
                    'WC_RDIAG_WOUND_TYPE_CD' => $currentWoundType->WC_WTYPE_CODE,
                    'WC_RDIAG_DESCRIPTION' => 'جرح في القدم اليمنى، ما زال تحت المتابعة والغيار الدوري.',
                    'WC_RDIAG_CLINICAL_EMP_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_RDIAG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                ],
            );

            $historicalWound = WcRegDiagnosis::query()->updateOrCreate(
                ['WC_RDIAG_CODE' => '11666666-6666-4666-8666-666666666662'],
                [
                    'WC_RDIAG_REGISTRATION_CD' => $historicalRegistration->WC_REG_CODE,
                    'WC_RDIAG_DIAGNOSIS_CD' => $historicalDiagnosis->WC_DIAG_CODE,
                    'WC_RDIAG_WOUND_TYPE_CD' => $historicalWoundType->WC_WTYPE_CODE,
                    'WC_RDIAG_DESCRIPTION' => 'جرح جراحي سابق اكتمل التئامه قبل تخريج المريض.',
                    'WC_RDIAG_CLINICAL_EMP_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_RDIAG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                ],
            );

            $pendingAppointment = WcScheduledReview::query()->updateOrCreate(
                ['WC_SREV_CODE' => '11777777-7777-4777-8777-777777777771'],
                [
                    'WC_SREV_REGISTRATION_CD' => $currentRegistration->WC_REG_CODE,
                    'WC_SREV_CREATED_BY_CD' => $entryUser->PER_USER_CODE,
                    'WC_SREV_SCHEDULED_DATE' => now()->addDays(4)->setTime(10, 0),
                    'WC_SREV_VISIT_SERIAL' => 3,
                    'WC_SREV_STATUS' => 'PENDING',
                    'WC_SREV_NOTES' => 'موعد قادم لتقييم الجرح الحالي وتغيير الضماد.',
                    'WC_SREV_CREATED_ON' => now()->subDays(2),
                    'WC_SREV_COMPLETED_ON' => null,
                ],
            );

            WcScheduledReview::query()->updateOrCreate(
                ['WC_SREV_CODE' => '11777777-7777-4777-8777-777777777772'],
                [
                    'WC_SREV_REGISTRATION_CD' => $currentRegistration->WC_REG_CODE,
                    'WC_SREV_CREATED_BY_CD' => $entryUser->PER_USER_CODE,
                    'WC_SREV_SCHEDULED_DATE' => now()->subDays(8)->setTime(10, 0),
                    'WC_SREV_VISIT_SERIAL' => 2,
                    'WC_SREV_STATUS' => 'MISSED',
                    'WC_SREV_NOTES' => 'موعد سابق لم يحضر إليه المريض.',
                    'WC_SREV_CREATED_ON' => now()->subDays(12),
                    'WC_SREV_COMPLETED_ON' => null,
                ],
            );

            $historicalAppointment = WcScheduledReview::query()->updateOrCreate(
                ['WC_SREV_CODE' => '11777777-7777-4777-8777-777777777773'],
                [
                    'WC_SREV_REGISTRATION_CD' => $historicalRegistration->WC_REG_CODE,
                    'WC_SREV_CREATED_BY_CD' => $entryUser->PER_USER_CODE,
                    'WC_SREV_SCHEDULED_DATE' => $historicalDischargedAt,
                    'WC_SREV_VISIT_SERIAL' => 4,
                    'WC_SREV_STATUS' => 'COMPLETED',
                    'WC_SREV_NOTES' => 'زيارة التخريج النهائية للتسجيل السابق.',
                    'WC_SREV_CREATED_ON' => $historicalDischargedAt->copy()->subDays(7),
                    'WC_SREV_COMPLETED_ON' => $historicalDischargedAt,
                ],
            );

            $currentOlderReview = WcDailyReview::query()->updateOrCreate(
                ['WC_DREV_CODE' => '11888888-8888-4888-8888-888888888881'],
                [
                    'WC_DREV_REGISTRATION_CD' => $currentRegistration->WC_REG_CODE,
                    'WC_DREV_SCHEDULED_REVIEW_CD' => null,
                    'WC_DREV_VISIT_DATE' => now()->subDays(18)->setTime(10, 0),
                    'WC_DREV_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_DREV_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY',
                    'WC_DREV_GENERAL_NOTES' => 'كانت الإفرازات أكثر في بداية التسجيل الحالي.',
                ],
            );

            $currentRecentReview = WcDailyReview::query()->updateOrCreate(
                ['WC_DREV_CODE' => '11888888-8888-4888-8888-888888888882'],
                [
                    'WC_DREV_REGISTRATION_CD' => $currentRegistration->WC_REG_CODE,
                    'WC_DREV_SCHEDULED_REVIEW_CD' => null,
                    'WC_DREV_VISIT_DATE' => now()->subDays(4)->setTime(10, 0),
                    'WC_DREV_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_DREV_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_DREV_OVERALL_STATUS' => 'IMPROVED_STAY',
                    'WC_DREV_GENERAL_NOTES' => 'انخفاض الإفرازات وتحسن الأنسجة في المراجعة الأخيرة.',
                ],
            );

            $historicalOlderReview = WcDailyReview::query()->updateOrCreate(
                ['WC_DREV_CODE' => '11888888-8888-4888-8888-888888888883'],
                [
                    'WC_DREV_REGISTRATION_CD' => $historicalRegistration->WC_REG_CODE,
                    'WC_DREV_SCHEDULED_REVIEW_CD' => null,
                    'WC_DREV_VISIT_DATE' => now()->subDays(205)->setTime(11, 0),
                    'WC_DREV_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_DREV_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_DREV_OVERALL_STATUS' => 'IMPROVED_SCHEDULED',
                    'WC_DREV_GENERAL_NOTES' => 'تحسن تدريجي للجرح ضمن التسجيل السابق.',
                ],
            );

            $historicalFinalReview = WcDailyReview::query()->updateOrCreate(
                ['WC_DREV_CODE' => '11888888-8888-4888-8888-888888888884'],
                [
                    'WC_DREV_REGISTRATION_CD' => $historicalRegistration->WC_REG_CODE,
                    'WC_DREV_SCHEDULED_REVIEW_CD' => $historicalAppointment->WC_SREV_CODE,
                    'WC_DREV_VISIT_DATE' => $historicalDischargedAt,
                    'WC_DREV_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                    'WC_DREV_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
                    'WC_DREV_OVERALL_STATUS' => 'IMPROVED_DISCHARGE',
                    'WC_DREV_GENERAL_NOTES' => 'اكتمل الالتئام وتم تخريج المريض من التسجيل السابق.',
                ],
            );

            $currentOlderDetail = WcDailyReviewDetail::query()->updateOrCreate(
                ['WC_REVD_CODE' => '11999999-9999-4999-8999-999999999991'],
                [
                    'WC_REVD_REVIEW_CD' => $currentOlderReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $currentWound->WC_RDIAG_CODE,
                    'WC_DREVD_WOUND_DESCRIPTION' => 'جرح بعمق متوسط مع إفرازات ملحوظة.',
                    'WC_DREVD_WOUND_CHANGES' => 'لا يوجد تحسن كافٍ مقارنة بالتقييم الأولي.',
                    'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
                    'WC_DREVD_MEDICATION_CD' => $medication->WC_MED_CODE,
                    'WC_DREVD_STATUS' => 'WORSENED',
                    'WC_DREVD_FOLLOW_UP_REQUIRED' => true,
                ],
            );

            $currentRecentDetail = WcDailyReviewDetail::query()->updateOrCreate(
                ['WC_REVD_CODE' => '11999999-9999-4999-8999-999999999992'],
                [
                    'WC_REVD_REVIEW_CD' => $currentRecentReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $currentWound->WC_RDIAG_CODE,
                    'WC_DREVD_WOUND_DESCRIPTION' => 'الجرح أنظف مع ظهور نسيج حبيبي صحي.',
                    'WC_DREVD_WOUND_CHANGES' => 'تحسن واضح وانخفاض في الإفرازات.',
                    'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
                    'WC_DREVD_MEDICATION_CD' => $medication->WC_MED_CODE,
                    'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
                    'WC_DREVD_FOLLOW_UP_REQUIRED' => true,
                ],
            );

            WcDailyReviewDetail::query()->updateOrCreate(
                ['WC_REVD_CODE' => '11999999-9999-4999-8999-999999999993'],
                [
                    'WC_REVD_REVIEW_CD' => $historicalOlderReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $historicalWound->WC_RDIAG_CODE,
                    'WC_DREVD_WOUND_DESCRIPTION' => 'جرح جراحي نظيف وفي مرحلة الالتئام.',
                    'WC_DREVD_WOUND_CHANGES' => 'تراجع الاحمرار وتحسن حواف الجرح.',
                    'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
                    'WC_DREVD_MEDICATION_CD' => $medication->WC_MED_CODE,
                    'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
                    'WC_DREVD_FOLLOW_UP_REQUIRED' => true,
                ],
            );

            $historicalFinalDetail = WcDailyReviewDetail::query()->updateOrCreate(
                ['WC_REVD_CODE' => '11999999-9999-4999-8999-999999999994'],
                [
                    'WC_REVD_REVIEW_CD' => $historicalFinalReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $historicalWound->WC_RDIAG_CODE,
                    'WC_DREVD_WOUND_DESCRIPTION' => 'التئام كامل دون علامات التهاب.',
                    'WC_DREVD_WOUND_CHANGES' => 'إغلاق كامل للجرح مقارنة بالمراجعة السابقة.',
                    'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
                    'WC_DREVD_MEDICATION_CD' => null,
                    'WC_DREVD_STATUS' => 'IMPROVED_DISCHARGE',
                    'WC_DREVD_FOLLOW_UP_REQUIRED' => false,
                ],
            );

            $this->seedPhoto(
                '12000000-0000-4000-8000-000000000001',
                $currentWound,
                null,
                'INITIAL',
                'wound_photos/demo/longitudinal-current-initial.svg',
                'صورة أولية تجريبية للجرح في التسجيل الحالي.',
                $currentOpenedAt,
                $clinicalEmployee,
                $entryUser,
            );
            $this->seedPhoto(
                '12000000-0000-4000-8000-000000000002',
                $currentWound,
                $currentRecentDetail,
                'REVIEW',
                'wound_photos/demo/longitudinal-current-review.svg',
                'صورة متابعة تجريبية تظهر التحسن في التسجيل الحالي.',
                now()->subDays(4)->setTime(10, 15),
                $clinicalEmployee,
                $entryUser,
            );
            $this->seedPhoto(
                '12000000-0000-4000-8000-000000000003',
                $historicalWound,
                null,
                'INITIAL',
                'wound_photos/demo/longitudinal-history-initial.svg',
                'صورة أولية تجريبية للجرح في التسجيل السابق.',
                $historicalOpenedAt,
                $clinicalEmployee,
                $entryUser,
            );
            $this->seedPhoto(
                '12000000-0000-4000-8000-000000000004',
                $historicalWound,
                $historicalFinalDetail,
                'REVIEW',
                'wound_photos/demo/longitudinal-history-review.svg',
                'صورة المتابعة النهائية في التسجيل السابق.',
                $historicalDischargedAt,
                $clinicalEmployee,
                $entryUser,
            );

            return $patient;
        });

        $this->storeDemoImages();

        $this->command?->info("Longitudinal demo patient seeded: {$patient->MRP_ID} ({$patient->MRP_PATREC_CODE})");
    }

    private function seedPhoto(
        string $code,
        WcRegDiagnosis $wound,
        ?WcDailyReviewDetail $detail,
        string $stage,
        string $path,
        string $caption,
        mixed $takenAt,
        Employee $clinicalEmployee,
        PerUser $entryUser,
    ): void {
        WcWoundPhoto::query()->updateOrCreate(
            ['WC_PHOTO_CODE' => $code],
            [
                'WC_PHOTO_REG_DIAGNOSIS_CD' => $wound->WC_RDIAG_CODE,
                'WC_PHOTO_REVIEW_DETAIL_CD' => $detail?->WC_REVD_CODE,
                'WC_PHOTO_STAGE' => $stage,
                'WC_PHOTO_PATH' => $path,
                'WC_PHOTO_CAPTION' => $caption,
                'WC_PHOTO_TAKEN_AT' => $takenAt,
                'WC_PHOTO_NURSE_CD' => $clinicalEmployee->EMP_SERIAL,
                'WC_PHOTO_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
            ],
        );
    }

    private function storeDemoImages(): void
    {
        $images = [
            'wound_photos/demo/longitudinal-current-initial.svg' => $this->demoImageSvg(
                'CURRENT REGISTRATION',
                'Baseline photo',
                '#fff7ed',
                '#ea580c',
            ),
            'wound_photos/demo/longitudinal-current-review.svg' => $this->demoImageSvg(
                'CURRENT REGISTRATION',
                'Follow-up photo',
                '#ecfdf5',
                '#059669',
            ),
            'wound_photos/demo/longitudinal-history-initial.svg' => $this->demoImageSvg(
                'HISTORICAL REGISTRATION',
                'Baseline photo',
                '#eff6ff',
                '#2563eb',
            ),
            'wound_photos/demo/longitudinal-history-review.svg' => $this->demoImageSvg(
                'HISTORICAL REGISTRATION',
                'Discharge photo',
                '#f5f3ff',
                '#7c3aed',
            ),
        ];

        foreach ($images as $path => $contents) {
            Storage::disk('public')->put($path, $contents);
        }
    }

    private function demoImageSvg(
        string $title,
        string $subtitle,
        string $background,
        string $accent,
    ): string {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="960" height="640" viewBox="0 0 960 640" role="img" aria-labelledby="title description">
            <title id="title">{$title}</title>
            <desc id="description">{$subtitle}</desc>
            <rect width="960" height="640" fill="{$background}"/>
            <circle cx="480" cy="260" r="140" fill="{$accent}" opacity="0.14"/>
            <circle cx="480" cy="260" r="82" fill="{$accent}" opacity="0.32"/>
            <path d="M438 260h84M480 218v84" stroke="{$accent}" stroke-width="22" stroke-linecap="round"/>
            <text x="480" y="455" text-anchor="middle" fill="{$accent}" font-family="Arial, sans-serif" font-size="34" font-weight="700">{$title}</text>
            <text x="480" y="505" text-anchor="middle" fill="#475569" font-family="Arial, sans-serif" font-size="28">{$subtitle}</text>
            <text x="480" y="560" text-anchor="middle" fill="#64748b" font-family="Arial, sans-serif" font-size="20">WCCMS demo image</text>
        </svg>
        SVG;
    }
}
