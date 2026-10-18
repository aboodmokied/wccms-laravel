<?php

namespace App\Http\Requests\Concerns;

use App\Models\WcAssessmentChoice;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

trait ValidatesWoundAssessment
{
    /** @return array<string, mixed> */
    protected function woundAssessmentRules(string $prefix, bool $initial): array
    {
        $rules = [
            "{$prefix}.wound_status_choice_code" => ['required', 'uuid', $this->activeChoiceRule('WOUND_STATUS')],
            "{$prefix}.wound_bed_choice_codes" => ['required', 'array', 'min:1'],
            "{$prefix}.wound_bed_choice_codes.*" => ['required', 'uuid', 'distinct', $this->activeChoiceRule('WOUND_BED')],
            "{$prefix}.wound_margin_choice_codes" => [$initial ? 'required' : 'nullable', 'array', $initial ? 'min:1' : 'min:0'],
            "{$prefix}.wound_margin_choice_codes.*" => ['required', 'uuid', 'distinct', $this->activeChoiceRule('WOUND_MARGIN')],
            "{$prefix}.exudate_amount_choice_code" => ['required', 'uuid', $this->activeChoiceRule('EXUDATE_AMOUNT')],
            "{$prefix}.exudate_type_choice_code" => ['nullable', 'uuid', $this->activeChoiceRule('EXUDATE_TYPE')],
            "{$prefix}.odor_choice_code" => ['required', 'uuid', $this->activeChoiceRule('ODOR')],
            "{$prefix}.pain_severity_choice_code" => ['required', 'uuid', $this->activeChoiceRule('PAIN_SEVERITY')],
            "{$prefix}.pain_at_dressing" => ['required', 'boolean'],
            "{$prefix}.length_mm" => [$initial ? 'required' : 'nullable', 'numeric', 'gt:0', 'max:1000', 'decimal:0,2'],
            "{$prefix}.width_mm" => [$initial ? 'required' : 'nullable', 'numeric', 'gt:0', 'max:1000', 'decimal:0,2'],
            "{$prefix}.depth_mm" => [$initial ? 'required' : 'nullable', 'numeric', 'min:0', 'max:1000', 'decimal:0,2'],
            "{$prefix}.dressing_code" => ['nullable', 'uuid', Rule::exists('wc_dressing_tb', 'WC_DRESS_CODE')->where('WC_DRESS_IS_ACTIVE', 1)],
            "{$prefix}.medication_code" => ['nullable', 'uuid', Rule::exists('wc_medication_tb', 'WC_MED_CODE')->where('WC_MED_IS_ACTIVE', 1)],
            "{$prefix}.procedure_code" => ['nullable', 'uuid', Rule::exists('wc_procedure_tb', 'WC_PROC_CODE')->where('WC_PROC_IS_ACTIVE', 1)],
            "{$prefix}.treatment_notes" => ['nullable', 'string', 'max:2000'],
            "{$prefix}.wound_photos" => ['nullable', 'array'],
            "{$prefix}.wound_photos.*" => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];

        if ($initial) {
            $rules += [
                "{$prefix}.wound_type_code" => ['required', 'uuid', Rule::exists('wc_wound_type_tb', 'WC_WTYPE_CODE')->where('WC_WTYPE_IS_ACTIVE', 1)],
                "{$prefix}.diagnosis_code" => ['required', 'uuid', Rule::exists('wc_diagnosis_tb', 'WC_DIAG_CODE')->where('WC_DIAG_IS_ACTIVE', 1)],
                "{$prefix}.fever_choice_code" => ['required', 'uuid', $this->activeChoiceRule('FEVER')],
                "{$prefix}.location_choice_code" => ['required', 'uuid', $this->activeChoiceRule('LOCATION')],
                "{$prefix}.laterality_choice_code" => ['required', 'uuid', $this->activeChoiceRule('LATERALITY')],
                "{$prefix}.location_notes" => ['nullable', 'string', 'max:500'],
                "{$prefix}.swab_taken" => ['required', 'boolean'],
                "{$prefix}.nutrition_choice_code" => ['required', 'uuid', $this->activeChoiceRule('NUTRITION')],
            ];
        } else {
            $rules += [
                "{$prefix}.registration_diagnosis_code" => ['required', 'uuid', 'distinct'],
                "{$prefix}.clinical_observation" => ['nullable', 'string', 'max:5000'],
                "{$prefix}.wound_changes" => ['nullable', 'string', 'max:5000'],
                "{$prefix}.outcome_status" => ['required', 'string', 'in:IMPROVED_DISCHARGE,IMPROVED_FOLLOWUP,WORSENED,DECEASED'],
                "{$prefix}.follow_up_required" => ['required', 'boolean'],
            ];
        }

        return $rules;
    }

    protected function configureWoundAssessmentValidator(Validator $validator, string $collection, bool $initial): void
    {
        $validator->after(function (Validator $validator) use ($collection, $initial) {
            $items = $this->input($collection, []);
            $dryCode = $this->choiceCode('EXUDATE_AMOUNT', 'DRY');
            $noneMarginCode = $this->choiceCode('WOUND_MARGIN', 'NONE_OBSERVED');
            $initialStatusCode = $this->choiceCode('WOUND_STATUS', 'FIRST_VISIT');
            $otherLocationCode = $this->choiceCode('LOCATION', 'OTHER');

            foreach ($items as $index => $item) {
                $label = 'الجرح رقم '.($index + 1);
                $amountCode = $item['exudate_amount_choice_code'] ?? null;
                $typeCode = $item['exudate_type_choice_code'] ?? null;

                if ($amountCode === $dryCode && filled($typeCode)) {
                    $validator->errors()->add("{$collection}.{$index}.exudate_type_choice_code", "{$label}: لا يمكن اختيار نوع إفرازات للجرح الجاف.");
                }
                if ($amountCode && $amountCode !== $dryCode && blank($typeCode)) {
                    $validator->errors()->add("{$collection}.{$index}.exudate_type_choice_code", "{$label}: نوع الإفرازات مطلوب.");
                }

                $margins = $item['wound_margin_choice_codes'] ?? [];
                if ($noneMarginCode && in_array($noneMarginCode, $margins, true) && count($margins) > 1) {
                    $validator->errors()->add("{$collection}.{$index}.wound_margin_choice_codes", "{$label}: خيار لا توجد علامة لا يجتمع مع علامات أخرى.");
                }

                $hasTreatment = filled($item['dressing_code'] ?? null)
                    || filled($item['medication_code'] ?? null)
                    || filled($item['procedure_code'] ?? null)
                    || filled($item['treatment_notes'] ?? null);
                if (! $hasTreatment) {
                    $validator->errors()->add("{$collection}.{$index}.treatment_notes", "{$label}: يجب توثيق علاج واحد على الأقل.");
                }

                $statusCode = $item['wound_status_choice_code'] ?? null;
                if ($initial && $statusCode && $statusCode !== $initialStatusCode) {
                    $validator->errors()->add("{$collection}.{$index}.wound_status_choice_code", "{$label}: الحالة الأولية يجب أن تكون الزيارة الأولى.");
                }
                if (! $initial && $statusCode === $initialStatusCode) {
                    $validator->errors()->add("{$collection}.{$index}.wound_status_choice_code", "{$label}: الزيارة الأولى غير متاحة في المتابعة.");
                }

                if ($initial && ($item['location_choice_code'] ?? null) === $otherLocationCode
                    && blank($item['location_notes'] ?? null)) {
                    $validator->errors()->add("{$collection}.{$index}.location_notes", "{$label}: يرجى تحديد الموقع الدقيق عند اختيار أخرى.");
                }
            }
        });
    }

    protected function activeChoiceRule(string $category): Exists
    {
        return Rule::exists('wc_assessment_choice_tb', 'WC_ACH_CODE')
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_IS_ACTIVE', 1);
    }

    private function choiceCode(string $category, string $key): ?string
    {
        return WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_KEY', $key)
            ->value('WC_ACH_CODE');
    }
}
