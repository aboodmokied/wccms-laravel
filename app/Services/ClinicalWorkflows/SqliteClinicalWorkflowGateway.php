<?php

namespace App\Services\ClinicalWorkflows;

use App\Exceptions\ClinicalWorkflowException;
use App\Models\WcAssessmentChoice;
use App\Models\WcAssessmentFinding;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRegistrationPmh;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Models\WcWoundAssessment;
use App\Models\WcWoundPhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SqliteClinicalWorkflowGateway implements ClinicalWorkflowGatewayInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function acceptRegistration(WcRequestReg $request, array $payload): WcRegistration
    {
        if (! in_array($request->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS'], true)) {
            throw new ClinicalWorkflowException('طلب التسجيل هذا تم البت فيه سابقاً.');
        }

        if ($request->registration()->exists()) {
            throw new ClinicalWorkflowException('يوجد تسجيل مرتبط بهذا الطلب بالفعل.');
        }

        $diagnoses = $payload['diagnoses'] ?? [];
        if (! is_array($diagnoses) || $diagnoses === []) {
            throw new ClinicalWorkflowException('يجب إدخال تقييم أولي كامل لجرح واحد على الأقل.');
        }

        foreach ($diagnoses as $assessment) {
            $this->validateAssessment($assessment, true);
        }
        $this->validateSharedRegistrationContext($payload);

        return DB::transaction(function () use ($request, $payload, $diagnoses) {
            $request->update([
                'WC_REQ_STATUS' => 'ACCEPTED',
                'WC_REQ_REVIEWED_BY_CD' => $payload['clinic_nurse_code'],
                'WC_REQ_REVIEWED_ON' => now(),
            ]);

            $registration = WcRegistration::create([
                'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
                'WC_REG_PATIENT_CD' => $request->WC_REQ_PATIENT_CD,
                'WC_REG_DEPARTMENT_CD' => $payload['department_code'],
                'WC_REG_PROCEDURE_CD' => $payload['procedure_code'] ?? null,
                'WC_REG_PMH_NOTES' => $payload['pmh_notes'] ?? null,
                'WC_REG_HAS_ALLERGIES' => $payload['has_allergies'],
                'WC_REG_ALLERGIES_DESCRIPTION' => $payload['allergies_description'] ?? null,
                'WC_REG_NO_COMORBIDITIES' => $payload['no_comorbidities'],
                'WC_REG_NEXT_VISIT_DATE' => $payload['next_visit_date'] ?? null,
                'WC_REG_FOLLOW_UP_FREQUENCY' => $payload['follow_up_frequency'] ?? null,
                'WC_REG_FOLLOW_UP_OBJECTIVE' => $payload['follow_up_objective'],
                'WC_REG_DATE' => $payload['assessed_at'] ?? now(),
                'WC_REG_NURSE_CD' => $payload['clinic_nurse_code'],
                'WC_REG_ENTRY_USER_CD' => $payload['entry_user_code'],
                'WC_REG_STATUS' => 'ACTIVE',
            ]);

            foreach ($payload['pmh_codes'] ?? [] as $pmhCode) {
                $this->assertActiveMaster(WcPmh::class, 'WC_PMH_CODE', 'WC_PMH_IS_ACTIVE', $pmhCode, 'الحالة المرضية');
                WcRegistrationPmh::create([
                    'WC_RPMH_REGISTRATION_CD' => $registration->WC_REG_CODE,
                    'WC_RPMH_PMH_CD' => $pmhCode,
                ]);
            }

            foreach ($diagnoses as $assessmentPayload) {
                $registrationDiagnosis = WcRegDiagnosis::create([
                    'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
                    'WC_RDIAG_WOUND_TYPE_CD' => $assessmentPayload['wound_type_code'],
                    'WC_RDIAG_DIAGNOSIS_CD' => $assessmentPayload['diagnosis_code'],
                    'WC_RDIAG_DESCRIPTION' => $assessmentPayload['location_notes'] ?? null,
                    'WC_RDIAG_CLINICAL_EMP_CD' => $payload['clinic_nurse_code'],
                    'WC_RDIAG_ENTRY_USER_CD' => $payload['entry_user_code'],
                ]);

                $this->createAssessment($registrationDiagnosis, $assessmentPayload, $payload, 'INITIAL');
            }

            $this->scheduleNextVisit($registration, $payload);

            $oldRegistrationCodes = WcRegistration::query()
                ->where('WC_REG_PATIENT_CD', $registration->WC_REG_PATIENT_CD)
                ->where('WC_REG_CODE', '!=', $registration->WC_REG_CODE)
                ->pluck('WC_REG_CODE');
            WcScheduledReview::query()
                ->whereIn('WC_SREV_REGISTRATION_CD', $oldRegistrationCodes)
                ->where('WC_SREV_STATUS', 'PENDING')
                ->delete();

            return $registration;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function addWound(WcRegistration $registration, array $payload): WcRegDiagnosis
    {
        if ($registration->WC_REG_STATUS !== 'ACTIVE') {
            throw new ClinicalWorkflowException('لا يمكن إضافة جرح إلى تسجيل مخرج.');
        }

        $assessmentPayload = $payload['assessment'] ?? $payload;
        $this->validateAssessment($assessmentPayload, true);

        return DB::transaction(function () use ($registration, $payload, $assessmentPayload) {
            $registrationDiagnosis = WcRegDiagnosis::create([
                'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
                'WC_RDIAG_WOUND_TYPE_CD' => $assessmentPayload['wound_type_code'],
                'WC_RDIAG_DIAGNOSIS_CD' => $assessmentPayload['diagnosis_code'],
                'WC_RDIAG_DESCRIPTION' => $assessmentPayload['location_notes'] ?? null,
                'WC_RDIAG_CLINICAL_EMP_CD' => $payload['clinic_nurse_code'],
                'WC_RDIAG_ENTRY_USER_CD' => $payload['entry_user_code'],
            ]);

            $this->createAssessment($registrationDiagnosis, $assessmentPayload, $payload, 'INITIAL');

            return $registrationDiagnosis;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function completeReview(
        WcRegistration $registration,
        array $payload,
        ?WcScheduledReview $scheduledReview = null
    ): WcDailyReview {
        $submissionKey = $payload['submission_key'];
        $existing = WcDailyReview::query()->where('WC_DREV_SUBMISSION_KEY', $submissionKey)->first();
        if ($existing) {
            if ($existing->WC_DREV_REGISTRATION_CD !== $registration->WC_REG_CODE) {
                throw new ClinicalWorkflowException('مفتاح الإرسال مستخدم لمراجعة أخرى.');
            }

            return $existing;
        }

        if ($scheduledReview) {
            if ($scheduledReview->WC_SREV_REGISTRATION_CD !== $registration->WC_REG_CODE) {
                throw new ClinicalWorkflowException('الموعد لا يتبع التسجيل المحدد.');
            }
            if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
                throw new ClinicalWorkflowException('لا يمكن إكمال موعد غير معلق.');
            }
        } elseif ($registration->WC_REG_STATUS !== 'ACTIVE') {
            throw new ClinicalWorkflowException('لا يمكن إضافة مراجعة يومية لتسجيل مخرج.');
        }

        $details = $payload['details'] ?? [];
        $expectedDiagnosisCodes = $registration->diagnoses()->pluck('WC_RDIAG_CODE')->sort()->values()->all();
        $submittedDiagnosisCodes = collect($details)
            ->map(fn (array $detail) => $detail['registration_diagnosis_code'] ?? $detail['diagnosis_code'] ?? null)
            ->filter()
            ->values();

        if ($submittedDiagnosisCodes->duplicates()->isNotEmpty()
            || $submittedDiagnosisCodes->sort()->values()->all() !== $expectedDiagnosisCodes) {
            throw new ClinicalWorkflowException('يجب إدخال تقييم واحد كامل لكل جرح في التسجيل.');
        }

        foreach ($details as $detail) {
            $this->validateAssessment($detail, false);
        }

        return DB::transaction(function () use ($registration, $payload, $scheduledReview, $details) {
            $dailyReview = WcDailyReview::create([
                'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
                'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduledReview?->WC_SREV_CODE,
                'WC_DREV_SUBMISSION_KEY' => $payload['submission_key'],
                'WC_DREV_VISIT_DATE' => $payload['assessed_at'] ?? now(),
                'WC_DREV_NURSE_CD' => $payload['nurse_code'],
                'WC_DREV_ENTRY_USER_CD' => $payload['entry_user_code'],
                'WC_DREV_OVERALL_STATUS' => $payload['overall_status'],
                'WC_DREV_GENERAL_NOTES' => $payload['general_notes'] ?? null,
            ]);

            foreach ($details as $assessmentPayload) {
                $diagnosisCode = $assessmentPayload['registration_diagnosis_code'] ?? $assessmentPayload['diagnosis_code'];
                $registrationDiagnosis = $registration->diagnoses()->findOrFail($diagnosisCode);
                $detail = WcDailyReviewDetail::create([
                    'WC_REVD_REVIEW_CD' => $dailyReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => $scheduledReview ? 'SCHEDULED' : 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $registrationDiagnosis->WC_RDIAG_CODE,
                    'WC_DREVD_WOUND_DESCRIPTION' => $assessmentPayload['clinical_observation'] ?? $assessmentPayload['observation'] ?? null,
                    'WC_DREVD_WOUND_CHANGES' => $assessmentPayload['wound_changes'] ?? null,
                    'WC_DREVD_DRESSING_CD' => $assessmentPayload['dressing_code'] ?? null,
                    'WC_DREVD_MEDICATION_CD' => $assessmentPayload['medication_code'] ?? null,
                    'WC_DREVD_STATUS' => $assessmentPayload['outcome_status'],
                    'WC_DREVD_FOLLOW_UP_REQUIRED' => $assessmentPayload['follow_up_required'],
                ]);

                $this->createAssessment($registrationDiagnosis, $assessmentPayload, $payload, 'FOLLOW_UP', $detail);
            }

            if ($scheduledReview) {
                $scheduledReview->update([
                    'WC_SREV_STATUS' => 'COMPLETED',
                    'WC_SREV_COMPLETED_ON' => now(),
                ]);
            }

            $registration->update([
                'WC_REG_NEXT_VISIT_DATE' => $payload['next_visit_date'] ?? null,
                'WC_REG_FOLLOW_UP_FREQUENCY' => $payload['follow_up_frequency'] ?? null,
                'WC_REG_FOLLOW_UP_OBJECTIVE' => $payload['follow_up_objective'] ?? null,
            ]);
            $this->scheduleNextVisit($registration, $payload);

            if (in_array($payload['overall_status'], ['IMPROVED_DISCHARGE', 'WORSENED_ADMISSION', 'DEATH'], true)) {
                $registration->update([
                    'WC_REG_STATUS' => 'DISCHARGED',
                    'WC_REG_DISCHARGED_AT' => now(),
                    'WC_REG_DISCHARGED_BY_EMP_CD' => $payload['nurse_code'],
                ]);
            }

            return $dailyReview;
        });
    }

    /**
     * @param  array<string, mixed>  $assessmentPayload
     * @param  array<string, mixed>  $sharedPayload
     */
    private function createAssessment(
        WcRegDiagnosis $registrationDiagnosis,
        array $assessmentPayload,
        array $sharedPayload,
        string $type,
        ?WcDailyReviewDetail $reviewDetail = null
    ): WcWoundAssessment {
        if ($type === 'INITIAL' && $registrationDiagnosis->assessments()->where('WC_WA_TYPE', 'INITIAL')->exists()) {
            throw new ClinicalWorkflowException('يوجد تقييم أولي لهذا الجرح بالفعل.');
        }

        $assessment = WcWoundAssessment::create([
            'WC_WA_REG_DIAGNOSIS_CD' => $registrationDiagnosis->WC_RDIAG_CODE,
            'WC_WA_REVIEW_DETAIL_CD' => $reviewDetail?->WC_REVD_CODE,
            'WC_WA_TYPE' => $type,
            'WC_WA_CLINICAL_EMP_CD' => $sharedPayload['clinic_nurse_code'] ?? $sharedPayload['nurse_code'],
            'WC_WA_WCT_EMP_CD' => $sharedPayload['wct_employee_code'] ?? null,
            'WC_WA_DOCTOR_EMP_CD' => $sharedPayload['doctor_employee_code'] ?? null,
            'WC_WA_ENTRY_USER_CD' => $sharedPayload['entry_user_code'],
            'WC_WA_ASSESSED_AT' => $sharedPayload['assessed_at'] ?? now(),
            'WC_WA_WOUND_STATUS_CD' => $assessmentPayload['wound_status_choice_code'],
            'WC_WA_EXUDATE_AMOUNT_CD' => $assessmentPayload['exudate_amount_choice_code'],
            'WC_WA_EXUDATE_TYPE_CD' => $assessmentPayload['exudate_type_choice_code'] ?? null,
            'WC_WA_ODOR_CD' => $assessmentPayload['odor_choice_code'],
            'WC_WA_PAIN_SEVERITY_CD' => $assessmentPayload['pain_severity_choice_code'],
            'WC_WA_PAIN_AT_DRESSING' => $assessmentPayload['pain_at_dressing'],
            'WC_WA_FEVER_CD' => $assessmentPayload['fever_choice_code'] ?? null,
            'WC_WA_LENGTH_MM' => $assessmentPayload['length_mm'] ?? null,
            'WC_WA_WIDTH_MM' => $assessmentPayload['width_mm'] ?? null,
            'WC_WA_DEPTH_MM' => $assessmentPayload['depth_mm'] ?? null,
            'WC_WA_LOCATION_CD' => $assessmentPayload['location_choice_code'] ?? null,
            'WC_WA_LATERALITY_CD' => $assessmentPayload['laterality_choice_code'] ?? null,
            'WC_WA_LOCATION_NOTES' => $assessmentPayload['location_notes'] ?? null,
            'WC_WA_SWAB_TAKEN' => $assessmentPayload['swab_taken'] ?? null,
            'WC_WA_NUTRITION_CD' => $assessmentPayload['nutrition_choice_code'] ?? null,
            'WC_WA_DRESSING_CD' => $assessmentPayload['dressing_code'] ?? null,
            'WC_WA_MEDICATION_CD' => $assessmentPayload['medication_code'] ?? null,
            'WC_WA_PROCEDURE_CD' => $assessmentPayload['procedure_code'] ?? null,
            'WC_WA_TREATMENT_NOTES' => $assessmentPayload['treatment_notes'] ?? null,
            'WC_WA_CLINICAL_OBSERVATION' => $assessmentPayload['clinical_observation'] ?? $assessmentPayload['observation'] ?? null,
            'WC_WA_WOUND_CHANGES' => $assessmentPayload['wound_changes'] ?? null,
            'WC_WA_FOLLOW_UP_REQUIRED' => $assessmentPayload['follow_up_required'] ?? true,
        ]);

        foreach ([...$assessmentPayload['wound_bed_choice_codes'], ...($assessmentPayload['wound_margin_choice_codes'] ?? [])] as $choiceCode) {
            WcAssessmentFinding::create([
                'WC_AF_ASSESSMENT_CD' => $assessment->WC_WA_CODE,
                'WC_AF_CHOICE_CD' => $choiceCode,
            ]);
        }

        foreach ($assessmentPayload['photos'] ?? [] as $photo) {
            WcWoundPhoto::create([
                'WC_PHOTO_CODE' => $photo['code'] ?? (string) Str::uuid(),
                'WC_PHOTO_REG_DIAGNOSIS_CD' => $registrationDiagnosis->WC_RDIAG_CODE,
                'WC_PHOTO_DAILY_REVIEW_DETAIL_CD' => $reviewDetail?->WC_REVD_CODE,
                'WC_PHOTO_ASSESSMENT_CD' => $assessment->WC_WA_CODE,
                'WC_PHOTO_STAGE' => $type === 'INITIAL' ? 'INITIAL' : 'REVIEW',
                'WC_PHOTO_PATH' => $photo['path'],
                'WC_PHOTO_CAPTION' => $photo['caption'] ?? null,
                'WC_PHOTO_TAKEN_AT' => $photo['taken_at'] ?? now(),
                'WC_PHOTO_NURSE_CD' => $sharedPayload['clinic_nurse_code'] ?? $sharedPayload['nurse_code'],
                'WC_PHOTO_ENTRY_USER_CD' => $sharedPayload['entry_user_code'],
            ]);
        }

        return $assessment;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateAssessment(array $payload, bool $initial): void
    {
        $requiredCategories = [
            'wound_status_choice_code' => 'WOUND_STATUS',
            'exudate_amount_choice_code' => 'EXUDATE_AMOUNT',
            'odor_choice_code' => 'ODOR',
            'pain_severity_choice_code' => 'PAIN_SEVERITY',
        ];
        if ($initial) {
            $requiredCategories += [
                'fever_choice_code' => 'FEVER',
                'location_choice_code' => 'LOCATION',
                'nutrition_choice_code' => 'NUTRITION',
            ];
        }

        foreach ($requiredCategories as $field => $category) {
            $this->assertChoice($payload[$field] ?? null, $category);
        }
        if (! empty($payload['exudate_type_choice_code'])) {
            $this->assertChoice($payload['exudate_type_choice_code'], 'EXUDATE_TYPE');
        }
        if (! empty($payload['laterality_choice_code'])) {
            $this->assertChoice($payload['laterality_choice_code'], 'LATERALITY');
        }

        $bedChoices = $payload['wound_bed_choice_codes'] ?? [];
        if ($bedChoices === [] || count($bedChoices) !== count(array_unique($bedChoices))) {
            throw new ClinicalWorkflowException('يجب اختيار حالة واحدة على الأقل من سرير الجرح دون تكرار.');
        }
        foreach ($bedChoices as $choiceCode) {
            $this->assertChoice($choiceCode, 'WOUND_BED');
        }

        $marginChoices = $payload['wound_margin_choice_codes'] ?? [];
        if ($initial && $marginChoices === []) {
            throw new ClinicalWorkflowException('يجب اختيار حالة حواف الجرح.');
        }
        foreach ($marginChoices as $choiceCode) {
            $this->assertChoice($choiceCode, 'WOUND_MARGIN');
        }
        $noneMargin = WcAssessmentChoice::query()->where('WC_ACH_CATEGORY', 'WOUND_MARGIN')->where('WC_ACH_KEY', 'NONE_OBSERVED')->value('WC_ACH_CODE');
        if ($noneMargin && in_array($noneMargin, $marginChoices, true) && count($marginChoices) > 1) {
            throw new ClinicalWorkflowException('لا يمكن الجمع بين عدم وجود علامة وحالة أخرى لحواف الجرح.');
        }

        $exudateAmount = WcAssessmentChoice::query()->find($payload['exudate_amount_choice_code']);
        if ($exudateAmount?->WC_ACH_KEY === 'DRY' && ! empty($payload['exudate_type_choice_code'])) {
            throw new ClinicalWorkflowException('لا يمكن تحديد نوع إفرازات عندما يكون الجرح جافاً.');
        }
        if ($exudateAmount?->WC_ACH_KEY !== 'DRY' && empty($payload['exudate_type_choice_code'])) {
            throw new ClinicalWorkflowException('نوع الإفرازات مطلوب عندما لا يكون الجرح جافاً.');
        }

        foreach (['length_mm', 'width_mm', 'depth_mm'] as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                if ($initial) {
                    throw new ClinicalWorkflowException('قياسات الجرح الأولية مطلوبة.');
                }

                continue;
            }
            $value = (float) $payload[$field];
            $minimum = $field === 'depth_mm' ? 0 : 0.00001;
            if ($value < $minimum || $value > 1000 || ! preg_match('/^\d{1,4}(\.\d{1,2})?$/', (string) $payload[$field])) {
                throw new ClinicalWorkflowException('قياس الجرح خارج الحدود المسموحة.');
            }
        }

        if (empty($payload['dressing_code']) && empty($payload['medication_code'])
            && empty($payload['procedure_code']) && blank($payload['treatment_notes'] ?? null)) {
            throw new ClinicalWorkflowException('يجب توثيق علاج واحد على الأقل للجرح.');
        }

        if (! empty($payload['dressing_code'])) {
            $this->assertActiveMaster(WcDressing::class, 'WC_DRESS_CODE', 'WC_DRESS_IS_ACTIVE', $payload['dressing_code'], 'الضماد');
        }
        if (! empty($payload['medication_code'])) {
            $this->assertActiveMaster(WcMedication::class, 'WC_MED_CODE', 'WC_MED_IS_ACTIVE', $payload['medication_code'], 'الدواء');
        }
        if (! empty($payload['procedure_code'])) {
            $this->assertActiveMaster(WcProcedure::class, 'WC_PROC_CODE', 'WC_PROC_IS_ACTIVE', $payload['procedure_code'], 'الإجراء');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function validateSharedRegistrationContext(array $payload): void
    {
        if ((bool) $payload['has_allergies'] && blank($payload['allergies_description'] ?? null)) {
            throw new ClinicalWorkflowException('وصف الحساسية مطلوب عند وجود حساسية.');
        }
        if (! (bool) $payload['has_allergies'] && filled($payload['allergies_description'] ?? null)) {
            throw new ClinicalWorkflowException('يجب ترك وصف الحساسية فارغاً عند عدم وجود حساسية.');
        }
        if ((bool) $payload['no_comorbidities'] && ($payload['pmh_codes'] ?? []) !== []) {
            throw new ClinicalWorkflowException('لا يمكن اختيار لا توجد مع حالات مرضية أخرى.');
        }
        if (empty($payload['next_visit_date']) && blank($payload['follow_up_frequency'] ?? null)) {
            throw new ClinicalWorkflowException('يجب تحديد تاريخ أو تكرار المتابعة.');
        }
        if (blank($payload['follow_up_objective'] ?? null)) {
            throw new ClinicalWorkflowException('الهدف الرئيسي للمتابعة مطلوب.');
        }
    }

    private function assertChoice(?string $code, string $category): void
    {
        $valid = $code && WcAssessmentChoice::query()
            ->where('WC_ACH_CODE', $code)
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_IS_ACTIVE', true)
            ->exists();

        if (! $valid) {
            throw new ClinicalWorkflowException("الخيار السريري غير صالح لفئة {$category}.");
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function assertActiveMaster(
        string $modelClass,
        string $keyColumn,
        string $activeColumn,
        string $code,
        string $label
    ): void {
        if (! $modelClass::query()->where($keyColumn, $code)->where($activeColumn, true)->exists()) {
            throw new ClinicalWorkflowException("{$label} غير صالح أو غير فعال.");
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function scheduleNextVisit(WcRegistration $registration, array $payload): void
    {
        if (empty($payload['next_visit_date'])) {
            return;
        }

        $serial = WcScheduledReview::query()
            ->where('WC_SREV_REGISTRATION_CD', $registration->WC_REG_CODE)
            ->max('WC_SREV_VISIT_SERIAL') ?? 0;

        WcScheduledReview::create([
            'WC_SREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_SREV_CREATED_BY_CD' => $payload['entry_user_code'],
            'WC_SREV_SCHEDULED_DATE' => $payload['next_visit_date'],
            'WC_SREV_VISIT_SERIAL' => $serial + 1,
            'WC_SREV_STATUS' => 'PENDING',
            'WC_SREV_NOTES' => $payload['follow_up_objective'] ?? null,
            'WC_SREV_CREATED_ON' => now(),
        ]);
    }
}
