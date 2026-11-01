<?php

namespace Database\Seeders;

use App\Models\AdmissionPatient;
use App\Models\Employee;
use App\Models\HospitalInfo;
use App\Models\HospitalLocation;
use App\Models\InpatientStay;
use App\Models\JobLookup;
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
use App\Models\WcWoundType;
use App\Support\Hospital;
use App\Support\LocalHospitalCopy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo data for a developer machine. The hospital rows (patients, employees, wards, admissions, C_SEX_TB,
 * C_HOSPITAL_TB) go into the LOCAL copies of the hospital tables, in the configured hospital (HOSPITAL_NO). With
 * HOSPITAL_SCHEMA set the hospital tables are the real ones and are never written to, so the seeder does nothing.
 */
class TestSeeder extends Seeder
{
    public function run(): void
    {
        if (Hospital::isExternal()) {
            $this->command?->warn('TestSeeder skipped: HOSPITAL_SCHEMA points at the real hospital tables, which WCCMS never writes to.');

            return;
        }

        // 1. Ensure Master Data exists
        $this->call([
            WcDepartmentSeeder::class,
            WcPmhSeeder::class,
            WcDiagnosisSeeder::class,
            WcWoundTypeSeeder::class,
            WcDressingSeeder::class,
            WcMedicationSeeder::class,
            WcProcedureSeeder::class,
        ]);

        // Fetch Master Data references
        $deptGeneral = WcDepartment::where('WC_DEPT_NAME', 'like', '%الجراحة%')->first() ?? WcDepartment::first();
        $deptInternal = WcDepartment::where('WC_DEPT_NAME', 'like', '%الباطنة%')->first() ?? WcDepartment::first();
        $deptICU = WcDepartment::where('WC_DEPT_NAME', 'like', '%العناية%')->first() ?? WcDepartment::first();

        $diagDiabetic = WcDiagnosis::where('WC_DIAG_NAME', 'like', '%سكري%')->first() ?? WcDiagnosis::first();
        $diagBedsores = WcDiagnosis::where('WC_DIAG_NAME', 'like', '%فراش%')->first() ?? WcDiagnosis::skip(1)->first() ?? WcDiagnosis::first();
        $diagBurn = WcDiagnosis::where('WC_DIAG_NAME', 'like', '%حروق%')->first() ?? WcDiagnosis::first();
        $diagSurgical = WcDiagnosis::where('WC_DIAG_NAME', 'like', '%جراحي%')->first() ?? WcDiagnosis::first();

        $wtypeDiabetic = WcWoundType::where('WC_WTYPE_NAME', 'like', '%سكري%')->first() ?? WcWoundType::first();
        $wtypePressure = WcWoundType::where('WC_WTYPE_NAME', 'like', '%ضغط%')->first() ?? WcWoundType::first();
        $wtypeBurn = WcWoundType::where('WC_WTYPE_NAME', 'like', '%حراري%')->first() ?? WcWoundType::first();
        $wtypeSurgical = WcWoundType::where('WC_WTYPE_NAME', 'like', '%جراحي%')->first() ?? WcWoundType::first();

        $dressingHydrogel = WcDressing::first();
        $dressingSilver = WcDressing::skip(1)->first() ?? WcDressing::first();

        $medAntibiotic = WcMedication::first();
        $medPainkiller = WcMedication::skip(1)->first() ?? WcMedication::first();

        $procCleaning = WcProcedure::first();
        $procDebridement = WcProcedure::skip(1)->first() ?? WcProcedure::first();

        // 2. Hospital lookups (local copies): this database's hospital, sex codes, a job title and two wards
        LocalHospitalCopy::firstOrInsert(
            HospitalInfo::class,
            ['H_CODE' => Hospital::number()],
            ['H_NAME_AR' => 'مجمع الشفاء الطبي', 'H_NAME_EN' => 'Al-Shifa Medical Complex', 'H_CURR' => 1, 'HOS_NO' => Hospital::number()]
        );
        // Demo codes only: WCCMS never assumes which code is male; reports classify by SEX_NAME_EN.
        LocalHospitalCopy::firstOrInsert(SexLookup::class, ['SEX_CODE' => 1], ['SEX_NAME_AR' => 'انثى', 'SEX_NAME_EN' => 'Female']);
        LocalHospitalCopy::firstOrInsert(SexLookup::class, ['SEX_CODE' => 2], ['SEX_NAME_AR' => 'ذكر', 'SEX_NAME_EN' => 'Male']);
        $jobNurse = LocalHospitalCopy::firstOrInsert(JobLookup::class, ['JOB_CODE' => 1], ['JOB_NAME_AR' => 'ممرض', 'JOB_NAME_EN' => 'Nurse']);
        $jobDoctor = LocalHospitalCopy::firstOrInsert(JobLookup::class, ['JOB_CODE' => 2], ['JOB_NAME_AR' => 'طبيب', 'JOB_NAME_EN' => 'Doctor']);
        $wardInternal = LocalHospitalCopy::firstOrInsert(HospitalLocation::class, ['LOC_CODE' => 101], ['LOC_NAME_AR' => 'الباطنة رجال', 'LOC_NAME_EN' => 'Internal Medicine (Male)']);
        $wardSurgery = LocalHospitalCopy::firstOrInsert(HospitalLocation::class, ['LOC_CODE' => 102], ['LOC_NAME_AR' => 'الجراحة العامة', 'LOC_NAME_EN' => 'General Surgery']);

        // 3. Users & Employees (EMPLOYEE_TB rows; the account links to EMP_SERIAL)
        $userAdmin = PerUser::firstOrCreate(
            ['PER_USER_USERNAME' => 'admin_user'],
            [
                'PER_USER_CODE' => (string) Str::uuid(),
                'PER_USER_ROLE' => 'ADMIN',
            ]
        );

        $empAdmin = $userAdmin->employee ?? $this->employee(9001, 'أحمد', 'السوري', $jobDoctor->JOB_CODE);
        $userAdmin->update(['PER_USER_EMP_CD' => $empAdmin->EMP_SERIAL, 'PER_USER_HOS_NO' => Hospital::number()]);

        $userSarah = PerUser::firstOrCreate(
            ['PER_USER_USERNAME' => 'nurse_sarah'],
            [
                'PER_USER_CODE' => (string) Str::uuid(),
                'PER_USER_ROLE' => 'NURSE',
            ]
        );

        $empSarah = $userSarah->employee ?? $this->employee(9002, 'سارة', 'أحمد', $jobNurse->JOB_CODE);
        $userSarah->update(['PER_USER_EMP_CD' => $empSarah->EMP_SERIAL, 'PER_USER_HOS_NO' => Hospital::number()]);

        $userKhaled = PerUser::firstOrCreate(
            ['PER_USER_USERNAME' => 'nurse_khaled'],
            [
                'PER_USER_CODE' => (string) Str::uuid(),
                'PER_USER_ROLE' => 'NURSE',
            ]
        );

        $empKhaled = $userKhaled->employee ?? $this->employee(9003, 'خالد', 'العلي', $jobNurse->JOB_CODE);
        $userKhaled->update(['PER_USER_EMP_CD' => $empKhaled->EMP_SERIAL, 'PER_USER_HOS_NO' => Hospital::number()]);

        // 4. Patients (MR_PATIENT_TB rows)
        $pat1 = $this->patient(1001, 400100101, 'عبدالله', 'المحمد', 2);
        $pat2 = $this->patient(1002, 400100102, 'مريم', 'العباسي', 1);
        $pat3 = $this->patient(1003, 400100103, 'يوسف', 'التميمي', 2);
        $pat4 = $this->patient(1004, 400100104, 'خالد', 'الزهراني', 2);
        $pat5 = $this->patient(1005, 400100105, 'فاطمة', 'النجار', 1);

        // Open hospital admissions (ADMISSION_PATIENT_TB + a not-cancelled INPATIENT_ADM_TB stay): ward and doctor
        $this->admission(50001, $pat1, $wardInternal, $empAdmin, now()->subDays(14));
        $this->admission(50002, $pat2, $wardSurgery, $empAdmin, now()->subDays(8));

        // 5. Registration Requests
        // Request 1: APPROVED for patient 1001
        $req1 = WcRequestReg::firstOrCreate(
            ['WC_REQ_PATIENT_CD' => $pat1->MRP_PATREC_CODE, 'WC_REQ_STATUS' => 'APPROVED'],
            [
                'WC_REQ_CODE' => (string) Str::uuid(),
                'WC_REQ_PAT_NAME_SNAPSHOT' => $pat1->full_name,
                'WC_REQ_PAT_ID_SNAPSHOT' => (string) $pat1->MRP_ID,
                'WC_REQ_NURSE_CD' => $empKhaled->EMP_SERIAL,
                'WC_REQ_ENTRY_USER_CD' => $userKhaled->PER_USER_CODE,
                'WC_REQ_REVIEWED_BY_CD' => $empSarah->EMP_SERIAL,
                'WC_REQ_NOTES' => 'تحويل حالة قدم سكرية بحاجة لغيار دوري',
                'WC_REQ_CREATED_AT' => now()->subDays(12),
            ]
        );

        // Request 2: APPROVED for patient 1002
        $req2 = WcRequestReg::firstOrCreate(
            ['WC_REQ_PATIENT_CD' => $pat2->MRP_PATREC_CODE, 'WC_REQ_STATUS' => 'APPROVED'],
            [
                'WC_REQ_CODE' => (string) Str::uuid(),
                'WC_REQ_PAT_NAME_SNAPSHOT' => $pat2->full_name,
                'WC_REQ_PAT_ID_SNAPSHOT' => (string) $pat2->MRP_ID,
                'WC_REQ_NURSE_CD' => $empKhaled->EMP_SERIAL,
                'WC_REQ_ENTRY_USER_CD' => $userKhaled->PER_USER_CODE,
                'WC_REQ_REVIEWED_BY_CD' => $empSarah->EMP_SERIAL,
                'WC_REQ_NOTES' => 'حروق من الدرجة الثانية في الطرف العلوي الأيمن',
                'WC_REQ_CREATED_AT' => now()->subDays(6),
            ]
        );

        // Request 3: APPROVED for patient 1003
        $req3 = WcRequestReg::firstOrCreate(
            ['WC_REQ_PATIENT_CD' => $pat3->MRP_PATREC_CODE, 'WC_REQ_STATUS' => 'APPROVED'],
            [
                'WC_REQ_CODE' => (string) Str::uuid(),
                'WC_REQ_PAT_NAME_SNAPSHOT' => $pat3->full_name,
                'WC_REQ_PAT_ID_SNAPSHOT' => (string) $pat3->MRP_ID,
                'WC_REQ_NURSE_CD' => $empKhaled->EMP_SERIAL,
                'WC_REQ_ENTRY_USER_CD' => $userKhaled->PER_USER_CODE,
                'WC_REQ_REVIEWED_BY_CD' => $empSarah->EMP_SERIAL,
                'WC_REQ_NOTES' => 'جرح جراحي بعد عملية استئصال',
                'WC_REQ_CREATED_AT' => now()->subDays(25),
            ]
        );

        // Request 4: PENDING for patient 1004
        WcRequestReg::firstOrCreate(
            ['WC_REQ_PATIENT_CD' => $pat4->MRP_PATREC_CODE, 'WC_REQ_STATUS' => 'PENDING'],
            [
                'WC_REQ_CODE' => (string) Str::uuid(),
                'WC_REQ_PAT_NAME_SNAPSHOT' => $pat4->full_name,
                'WC_REQ_PAT_ID_SNAPSHOT' => (string) $pat4->MRP_ID,
                'WC_REQ_NURSE_CD' => $empKhaled->EMP_SERIAL,
                'WC_REQ_ENTRY_USER_CD' => $userKhaled->PER_USER_CODE,
                'WC_REQ_NOTES' => 'طلب تحويل حديث لقرحة في القدم اليسرى',
                'WC_REQ_CREATED_AT' => now()->subHours(4),
            ]
        );

        // Request 5: VISIT_IN_PROGRESS for patient 1005
        WcRequestReg::firstOrCreate(
            ['WC_REQ_PATIENT_CD' => $pat5->MRP_PATREC_CODE, 'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS'],
            [
                'WC_REQ_CODE' => (string) Str::uuid(),
                'WC_REQ_PAT_NAME_SNAPSHOT' => $pat5->full_name,
                'WC_REQ_PAT_ID_SNAPSHOT' => (string) $pat5->MRP_ID,
                'WC_REQ_NURSE_CD' => $empKhaled->EMP_SERIAL,
                'WC_REQ_ENTRY_USER_CD' => $userKhaled->PER_USER_CODE,
                'WC_REQ_REVIEWED_BY_CD' => $empSarah->EMP_SERIAL,
                'WC_REQ_NOTES' => 'زيارة التقييم جارية حالياً بالقسم الداخلي',
                'WC_REQ_CREATED_AT' => now()->subHours(2),
            ]
        );

        // 6. Registrations
        // Reg 1: Active for patient 1001
        $reg1 = WcRegistration::firstOrCreate(
            ['WC_REG_PATIENT_CD' => $pat1->MRP_PATREC_CODE, 'WC_REG_STATUS' => 'ACTIVE'],
            [
                'WC_REG_CODE' => (string) Str::uuid(),
                'WC_REG_REQUEST_CD' => $req1->WC_REQ_CODE,
                'WC_REG_DEPARTMENT_CD' => $deptInternal->WC_DEPT_CODE,
                'WC_REG_PROCEDURE_CD' => $procCleaning->WC_PROC_CODE,
                'WC_REG_PMH_NOTES' => 'مرض السكري من النوع الثاني + ارتفاع ضغط الدم',
                'WC_REG_DATE' => now()->subDays(10),
                'WC_REG_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_REG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        // Reg 2: Active for patient 1002
        $reg2 = WcRegistration::firstOrCreate(
            ['WC_REG_PATIENT_CD' => $pat2->MRP_PATREC_CODE, 'WC_REG_STATUS' => 'ACTIVE'],
            [
                'WC_REG_CODE' => (string) Str::uuid(),
                'WC_REG_REQUEST_CD' => $req2->WC_REQ_CODE,
                'WC_REG_DEPARTMENT_CD' => $deptGeneral->WC_DEPT_CODE,
                'WC_REG_PROCEDURE_CD' => $procDebridement->WC_PROC_CODE,
                'WC_REG_PMH_NOTES' => 'حساسية من البنسلين',
                'WC_REG_DATE' => now()->subDays(5),
                'WC_REG_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_REG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        // Reg 3: Discharged for patient 1003
        $reg3 = WcRegistration::firstOrCreate(
            ['WC_REG_PATIENT_CD' => $pat3->MRP_PATREC_CODE, 'WC_REG_STATUS' => 'DISCHARGED'],
            [
                'WC_REG_CODE' => (string) Str::uuid(),
                'WC_REG_REQUEST_CD' => $req3->WC_REQ_CODE,
                'WC_REG_DEPARTMENT_CD' => $deptICU->WC_DEPT_CODE,
                'WC_REG_PROCEDURE_CD' => $procCleaning->WC_PROC_CODE,
                'WC_REG_PMH_NOTES' => 'لا توجد أمراض مزمنة',
                'WC_REG_DATE' => now()->subDays(20),
                'WC_REG_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_REG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
                'WC_REG_DISCHARGED_AT' => now()->subDays(2),
                'WC_REG_DISCHARGED_BY_EMP_CD' => $empSarah->EMP_SERIAL,
            ]
        );

        // 7. Registration Diagnoses
        $rdiag1_1 = WcRegDiagnosis::firstOrCreate(
            ['WC_RDIAG_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_RDIAG_DIAGNOSIS_CD' => $diagDiabetic->WC_DIAG_CODE],
            [
                'WC_RDIAG_CODE' => (string) Str::uuid(),
                'WC_RDIAG_WOUND_TYPE_CD' => $wtypeDiabetic->WC_WTYPE_CODE,
                'WC_RDIAG_CLINICAL_EMP_CD' => $empSarah->EMP_SERIAL,
                'WC_RDIAG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        $rdiag1_2 = WcRegDiagnosis::firstOrCreate(
            ['WC_RDIAG_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_RDIAG_DIAGNOSIS_CD' => $diagBedsores->WC_DIAG_CODE],
            [
                'WC_RDIAG_CODE' => (string) Str::uuid(),
                'WC_RDIAG_WOUND_TYPE_CD' => $wtypePressure->WC_WTYPE_CODE,
                'WC_RDIAG_CLINICAL_EMP_CD' => $empSarah->EMP_SERIAL,
                'WC_RDIAG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        $rdiag2_1 = WcRegDiagnosis::firstOrCreate(
            ['WC_RDIAG_REGISTRATION_CD' => $reg2->WC_REG_CODE, 'WC_RDIAG_DIAGNOSIS_CD' => $diagBurn->WC_DIAG_CODE],
            [
                'WC_RDIAG_CODE' => (string) Str::uuid(),
                'WC_RDIAG_WOUND_TYPE_CD' => $wtypeBurn->WC_WTYPE_CODE,
                'WC_RDIAG_CLINICAL_EMP_CD' => $empSarah->EMP_SERIAL,
                'WC_RDIAG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        $rdiag3_1 = WcRegDiagnosis::firstOrCreate(
            ['WC_RDIAG_REGISTRATION_CD' => $reg3->WC_REG_CODE, 'WC_RDIAG_DIAGNOSIS_CD' => $diagSurgical->WC_DIAG_CODE],
            [
                'WC_RDIAG_CODE' => (string) Str::uuid(),
                'WC_RDIAG_WOUND_TYPE_CD' => $wtypeSurgical->WC_WTYPE_CODE,
                'WC_RDIAG_CLINICAL_EMP_CD' => $empSarah->EMP_SERIAL,
                'WC_RDIAG_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
            ]
        );

        // 8. Scheduled Reviews
        $srev1 = WcScheduledReview::firstOrCreate(
            ['WC_SREV_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_SREV_STATUS' => 'PENDING'],
            [
                'WC_SREV_CODE' => (string) Str::uuid(),
                'WC_SREV_CREATED_BY_CD' => $userSarah->PER_USER_CODE,
                'WC_SREV_SCHEDULED_DATE' => now()->addDay()->startOfDay(),
                'WC_SREV_VISIT_SERIAL' => 1,
                'WC_SREV_NOTES' => 'متابعة تقييم الجرح والغيار التالي',
                'WC_SREV_CREATED_ON' => now()->subDays(1),
            ]
        );

        $srev2 = WcScheduledReview::firstOrCreate(
            ['WC_SREV_REGISTRATION_CD' => $reg2->WC_REG_CODE, 'WC_SREV_STATUS' => 'PENDING'],
            [
                'WC_SREV_CODE' => (string) Str::uuid(),
                'WC_SREV_CREATED_BY_CD' => $userSarah->PER_USER_CODE,
                'WC_SREV_SCHEDULED_DATE' => now()->startOfDay(),
                'WC_SREV_VISIT_SERIAL' => 1,
                'WC_SREV_NOTES' => 'متابعة التئام حروق الطرف العلوي اليوم',
                'WC_SREV_CREATED_ON' => now()->subDays(2),
            ]
        );

        WcScheduledReview::firstOrCreate(
            ['WC_SREV_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_SREV_STATUS' => 'MISSED'],
            [
                'WC_SREV_CODE' => (string) Str::uuid(),
                'WC_SREV_CREATED_BY_CD' => $userSarah->PER_USER_CODE,
                'WC_SREV_SCHEDULED_DATE' => now()->subDays(4)->startOfDay(),
                'WC_SREV_VISIT_SERIAL' => 1,
                'WC_SREV_NOTES' => 'لم يحضر المريض للموعد المجدول',
                'WC_SREV_CREATED_ON' => now()->subDays(6),
            ]
        );

        $srev3 = WcScheduledReview::firstOrCreate(
            ['WC_SREV_REGISTRATION_CD' => $reg3->WC_REG_CODE, 'WC_SREV_STATUS' => 'COMPLETED'],
            [
                'WC_SREV_CODE' => (string) Str::uuid(),
                'WC_SREV_CREATED_BY_CD' => $userSarah->PER_USER_CODE,
                'WC_SREV_SCHEDULED_DATE' => now()->subDays(2)->startOfDay(),
                'WC_SREV_VISIT_SERIAL' => 1,
                'WC_SREV_NOTES' => 'موعد المتابعة الأخير والتخريج',
                'WC_SREV_CREATED_ON' => now()->subDays(5),
                'WC_SREV_COMPLETED_ON' => now()->subDays(2),
            ]
        );

        // 9. Daily Reviews (keyed by registration and outcome so a second run finds them again)
        // Review for Reg 1 (Day -7)
        $drev1 = WcDailyReview::firstOrCreate(
            ['WC_DREV_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY'],
            [
                'WC_DREV_VISIT_DATE' => now()->subDays(7)->format('Y-m-d H:i:s'),
                'WC_DREV_CODE' => (string) Str::uuid(),
                'WC_DREV_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_DREV_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
                'WC_DREV_GENERAL_NOTES' => 'جرح القدم السكرية يتطلب تنظيفاً مكثفاً',
            ]
        );

        WcDailyReviewDetail::firstOrCreate(
            ['WC_REVD_REVIEW_CD' => $drev1->WC_DREV_CODE, 'WC_DREVD_REG_DIAGNOSIS_CD' => $rdiag1_1->WC_RDIAG_CODE],
            [
                'WC_REVD_CODE' => (string) Str::uuid(),
                'WC_REVD_REVIEW_TYPE' => 'DAILY',
                'WC_DREVD_WOUND_DESCRIPTION' => 'جرح بعمق 0.5 سم مع وجود إفرازات قليل',
                'WC_DREVD_WOUND_CHANGES' => 'تغير في لون حواف الجرح',
                'WC_DREVD_DRESSING_CD' => $dressingHydrogel->WC_DRESS_CODE,
                'WC_DREVD_MEDICATION_CD' => $medAntibiotic->WC_MED_CODE,
                'WC_DREVD_STATUS' => 'WORSENED_STAY',
                'WC_DREVD_FOLLOW_UP_REQUIRED' => 1,
            ]
        );

        // Review for Reg 1 (Day -1)
        $drev2 = WcDailyReview::firstOrCreate(
            ['WC_DREV_REGISTRATION_CD' => $reg1->WC_REG_CODE, 'WC_DREV_OVERALL_STATUS' => 'IMPROVED_SCHEDULED'],
            [
                'WC_DREV_VISIT_DATE' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'WC_DREV_CODE' => (string) Str::uuid(),
                'WC_DREV_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_DREV_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
                'WC_DREV_GENERAL_NOTES' => 'تحسن ملحوظ في حواف الجرح وتراجع الإفرازات',
            ]
        );

        WcDailyReviewDetail::firstOrCreate(
            ['WC_REVD_REVIEW_CD' => $drev2->WC_DREV_CODE, 'WC_DREVD_REG_DIAGNOSIS_CD' => $rdiag1_1->WC_RDIAG_CODE],
            [
                'WC_REVD_CODE' => (string) Str::uuid(),
                'WC_REVD_REVIEW_TYPE' => 'DAILY',
                'WC_DREVD_WOUND_DESCRIPTION' => 'نظافة حواف الجرح وتكون أنسجة حمراء جديدة',
                'WC_DREVD_WOUND_CHANGES' => 'تحسن في نمو الأنسجة',
                'WC_DREVD_DRESSING_CD' => $dressingSilver->WC_DRESS_CODE,
                'WC_DREVD_MEDICATION_CD' => $medAntibiotic->WC_MED_CODE,
                'WC_DREVD_STATUS' => 'IMPROVED_SCHEDULED',
                'WC_DREVD_FOLLOW_UP_REQUIRED' => 1,
            ]
        );

        // Review for Reg 2 (Day -2)
        $drev3 = WcDailyReview::firstOrCreate(
            ['WC_DREV_REGISTRATION_CD' => $reg2->WC_REG_CODE, 'WC_DREV_OVERALL_STATUS' => 'IMPROVED_FOLLOWUP'],
            [
                'WC_DREV_VISIT_DATE' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'WC_DREV_CODE' => (string) Str::uuid(),
                'WC_DREV_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_DREV_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
                'WC_DREV_GENERAL_NOTES' => 'استجابة جيدة لضماد الحروق',
            ]
        );

        WcDailyReviewDetail::firstOrCreate(
            ['WC_REVD_REVIEW_CD' => $drev3->WC_DREV_CODE, 'WC_DREVD_REG_DIAGNOSIS_CD' => $rdiag2_1->WC_RDIAG_CODE],
            [
                'WC_REVD_CODE' => (string) Str::uuid(),
                'WC_REVD_REVIEW_TYPE' => 'DAILY',
                'WC_DREVD_WOUND_DESCRIPTION' => 'سطح الحرق يبدي التئاماً جزيئاً',
                'WC_DREVD_WOUND_CHANGES' => 'تراجع الاحمرار والانتفاخ',
                'WC_DREVD_DRESSING_CD' => $dressingSilver->WC_DRESS_CODE,
                'WC_DREVD_MEDICATION_CD' => $medPainkiller->WC_MED_CODE,
                'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
                'WC_DREVD_FOLLOW_UP_REQUIRED' => 1,
            ]
        );

        // Review for Reg 3 (Day -2, Discharge visit)
        $drev4 = WcDailyReview::firstOrCreate(
            ['WC_DREV_REGISTRATION_CD' => $reg3->WC_REG_CODE, 'WC_DREV_OVERALL_STATUS' => 'IMPROVED_DISCHARGE'],
            [
                'WC_DREV_VISIT_DATE' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'WC_DREV_CODE' => (string) Str::uuid(),
                'WC_DREV_SCHEDULED_REVIEW_CD' => $srev3->WC_SREV_CODE,
                'WC_DREV_NURSE_CD' => $empSarah->EMP_SERIAL,
                'WC_DREV_ENTRY_USER_CD' => $userSarah->PER_USER_CODE,
                'WC_DREV_GENERAL_NOTES' => 'التئام كامل للجرح وتخريج المريض بنجاح من العيادة',
            ]
        );

        WcDailyReviewDetail::firstOrCreate(
            ['WC_REVD_REVIEW_CD' => $drev4->WC_DREV_CODE, 'WC_DREVD_REG_DIAGNOSIS_CD' => $rdiag3_1->WC_RDIAG_CODE],
            [
                'WC_REVD_CODE' => (string) Str::uuid(),
                'WC_REVD_REVIEW_TYPE' => 'DAILY',
                'WC_DREVD_WOUND_DESCRIPTION' => 'التئام كامل لخط الجراحة وإزالة الغرز',
                'WC_DREVD_WOUND_CHANGES' => 'ندبة نظيفة بدون أي علامات التهاب',
                'WC_DREVD_DRESSING_CD' => $dressingHydrogel->WC_DRESS_CODE,
                'WC_DREVD_MEDICATION_CD' => null,
                'WC_DREVD_STATUS' => 'IMPROVED_DISCHARGE',
                'WC_DREVD_FOLLOW_UP_REQUIRED' => 0,
            ]
        );
    }

    private function employee(int $serial, string $firstName, string $lastName, int $jobCode): Employee
    {
        return LocalHospitalCopy::firstOrInsert(
            Employee::class,
            ['EMP_SERIAL' => $serial],
            [
                'EMP_FIRST_NAME_AR' => $firstName,
                'EMP_LAST_NAME_AR' => $lastName,
                'EMP_JOB_CD' => $jobCode,
                'EMP_ACTIVE' => config('hospital.employees.active_value'),
            ]
        );
    }

    private function patient(int $code, int $documentNumber, string $firstName, string $lastName, int $sexCode): MrPatient
    {
        return LocalHospitalCopy::firstOrInsert(
            MrPatient::class,
            ['MRP_PATREC_CODE' => $code],
            [
                'MRP_ID_TYPE_CD' => 1,
                'MRP_ID' => $documentNumber,
                'MRP_FIRST_NAME_AR' => $firstName,
                'MRP_LAST_NAME_AR' => $lastName,
                'MRP_SEX_CD' => $sexCode,
                'MRP_DOB' => now()->subYears(40 + $code % 30)->toDateString(),
                'MRP_MOBILE_NO' => '059'.str_pad((string) $code, 7, '0', STR_PAD_LEFT),
            ]
        );
    }

    private function admission(int $code, MrPatient $patient, HospitalLocation $ward, Employee $doctor, \DateTimeInterface $enteredAt): void
    {
        LocalHospitalCopy::firstOrInsert(
            AdmissionPatient::class,
            ['ADMISSION_CODE' => $code],
            [
                'ADMISSION_PATREC_CD' => $patient->MRP_PATREC_CODE,
                'ADMISSION_LOC_CD' => $ward->LOC_CODE,
                'ADMISSION_ENTER_DATE' => $enteredAt,
                'ADMISSION_ENTER_DOC_CD' => $doctor->EMP_SERIAL,
            ]
        );

        LocalHospitalCopy::firstOrInsert(
            InpatientStay::class,
            ['INPATIENT_CODE' => $code],
            [
                'INPATIENT_ADMISSION_CD' => $code,
                'INPATIENT_DATE' => $enteredAt,
                'INPATIENT_SUP_LOC_CD' => $ward->LOC_CODE,
                'INPATIENT_RESD_LOC_CD' => $ward->LOC_CODE,
                'INPATIENT_CANCELED' => config('hospital.admission.not_cancelled_value'),
            ]
        );
    }
}
