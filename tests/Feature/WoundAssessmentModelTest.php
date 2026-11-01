<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\PerUser;
use App\Models\WcAssessmentChoice;
use App\Models\WcAssessmentFinding;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcPmh;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRegistrationPmh;
use App\Models\WcRequestReg;
use App\Models\WcWoundAssessment;
use App\Models\WcWoundType;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WoundAssessmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_structured_assessment_schema_uses_db_first_keys_and_legacy_safe_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('wc_assessment_choice_tb', [
            'WC_ACH_CODE',
            'WC_ACH_CATEGORY',
            'WC_ACH_KEY',
            'WC_ACH_LABEL_AR',
            'WC_ACH_IS_ACTIVE',
            'WC_ACH_DISPLAY_ORDER',
        ]));

        $this->assertTrue(Schema::hasColumns('wc_wound_assessment_tb', [
            'WC_WA_CODE',
            'WC_WA_REG_DIAGNOSIS_CD',
            'WC_WA_REVIEW_DETAIL_CD',
            'WC_WA_TYPE',
            'WC_WA_CLINICAL_EMP_CD',
            'WC_WA_ENTRY_USER_CD',
            'WC_WA_ASSESSED_AT',
        ]));

        $this->assertTrue(Schema::hasColumns('wc_registration_tb', [
            'WC_REG_HAS_ALLERGIES',
            'WC_REG_ALLERGIES_DESCRIPTION',
            'WC_REG_NO_COMORBIDITIES',
            'WC_REG_NEXT_VISIT_DATE',
            'WC_REG_FOLLOW_UP_FREQUENCY',
            'WC_REG_FOLLOW_UP_OBJECTIVE',
        ]));

        $this->assertTrue(Schema::hasColumn('wc_daily_review_tb', 'WC_DREV_SUBMISSION_KEY'));
        $this->assertTrue(Schema::hasColumn('wc_wound_photo_tb', 'WC_PHOTO_ASSESSMENT_CD'));
    }

    public function test_choices_are_category_scoped_and_retired_choices_remain_readable(): void
    {
        [$registrationDiagnosis, $employee, $user] = $this->createRegistrationDiagnosis();

        $status = $this->installedChoice('WOUND_STATUS', 'FIRST_VISIT');
        $status->update(['WC_ACH_IS_ACTIVE' => 0]);
        $exudate = $this->installedChoice('EXUDATE_AMOUNT', 'DRY');
        $odor = $this->installedChoice('ODOR', 'NONE');
        $pain = $this->installedChoice('PAIN_SEVERITY', 'NONE');
        $bed = $this->installedChoice('WOUND_BED', 'GRANULATION');

        $assessment = WcWoundAssessment::create([
            'WC_WA_REG_DIAGNOSIS_CD' => $registrationDiagnosis->WC_RDIAG_CODE,
            'WC_WA_TYPE' => 'INITIAL',
            'WC_WA_CLINICAL_EMP_CD' => $employee->EMP_SERIAL,
            'WC_WA_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_WA_ASSESSED_AT' => now(),
            'WC_WA_WOUND_STATUS_CD' => $status->WC_ACH_CODE,
            'WC_WA_EXUDATE_AMOUNT_CD' => $exudate->WC_ACH_CODE,
            'WC_WA_ODOR_CD' => $odor->WC_ACH_CODE,
            'WC_WA_PAIN_SEVERITY_CD' => $pain->WC_ACH_CODE,
            'WC_WA_PAIN_AT_DRESSING' => false,
            'WC_WA_FOLLOW_UP_REQUIRED' => true,
        ]);

        WcAssessmentFinding::create([
            'WC_AF_ASSESSMENT_CD' => $assessment->WC_WA_CODE,
            'WC_AF_CHOICE_CD' => $bed->WC_ACH_CODE,
        ]);

        $this->assertSame('الزيارة الأولى', $assessment->fresh()->woundStatus->WC_ACH_LABEL_AR);
        $this->assertFalse($assessment->fresh()->woundStatus->WC_ACH_IS_ACTIVE);
        $this->assertCount(1, $assessment->fresh()->findings);

        $this->expectException(QueryException::class);
        $this->choice('WOUND_STATUS', 'FIRST_VISIT', 'قيمة مكررة');
    }

    public function test_registration_pmh_links_are_unique_and_legacy_context_remains_nullable(): void
    {
        [$registrationDiagnosis] = $this->createRegistrationDiagnosis();
        $registration = $registrationDiagnosis->registration;
        $pmh = WcPmh::create(['WC_PMH_NAME' => 'السكري', 'WC_PMH_IS_ACTIVE' => 1]);

        WcRegistrationPmh::create([
            'WC_RPMH_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RPMH_PMH_CD' => $pmh->WC_PMH_CODE,
        ]);

        $this->assertNull($registration->WC_REG_HAS_ALLERGIES);
        $this->assertNull($registration->WC_REG_NO_COMORBIDITIES);
        $this->assertCount(1, $registration->fresh()->pmhLinks);

        $this->expectException(QueryException::class);
        WcRegistrationPmh::create([
            'WC_RPMH_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RPMH_PMH_CD' => $pmh->WC_PMH_CODE,
        ]);
    }

    private function choice(string $category, string $key, string $label): WcAssessmentChoice
    {
        return WcAssessmentChoice::create([
            'WC_ACH_CATEGORY' => $category,
            'WC_ACH_KEY' => $key,
            'WC_ACH_LABEL_AR' => $label,
            'WC_ACH_IS_ACTIVE' => 1,
            'WC_ACH_DISPLAY_ORDER' => 1,
        ]);
    }

    /**
     * A choice installed by the core migration.
     */
    private function installedChoice(string $category, string $key): WcAssessmentChoice
    {
        return WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_KEY', $key)
            ->firstOrFail();
    }

    /**
     * @return array{WcRegDiagnosis, Employee, PerUser}
     */
    private function createRegistrationDiagnosis(): array
    {
        $user = PerUser::create([
            'PER_USER_USERNAME' => 'assessment-entry',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);
        $employee = Employee::factory()->named('ممرضة الاختبار')->create();
        $user->update(['PER_USER_EMP_CD' => $employee->EMP_SERIAL]);
        $patient = MrPatient::factory()->male()->named('مريض الاختبار')->create([
            'MRP_ID' => 500100104,
        ]);
        $department = WcDepartment::create(['WC_DEPT_NAME' => 'عيادة الجروح']);
        $request = WcRequestReg::create([
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);
        $registration = WcRegistration::create([
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $employee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);
        $diagnosis = WcDiagnosis::create(['WC_DIAG_NAME' => 'قرحة اختبار']);
        $woundType = WcWoundType::create(['WC_WTYPE_NAME' => 'جرح اختبار']);
        $registrationDiagnosis = WcRegDiagnosis::create([
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $woundType->WC_WTYPE_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $employee->EMP_SERIAL,
            'WC_RDIAG_ENTRY_USER_CD' => $user->PER_USER_CODE,
        ]);

        return [$registrationDiagnosis, $employee, $user];
    }
}
