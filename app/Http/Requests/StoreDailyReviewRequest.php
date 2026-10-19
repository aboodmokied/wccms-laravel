<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWoundAssessment;
use App\Models\WcRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyReviewRequest extends FormRequest
{
    use ValidatesWoundAssessment;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'submission_key' => ['required', 'uuid'],
            'nurse_code' => ['required', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'wct_employee_code' => ['nullable', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'doctor_employee_code' => ['nullable', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'overall_status' => ['required', Rule::in(['IMPROVED_DISCHARGE', 'IMPROVED_SCHEDULED', 'IMPROVED_STAY', 'WORSENED_SCHEDULED', 'WORSENED_ADMISSION', 'WORSENED_STAY', 'DEATH'])],
            'general_notes' => ['nullable', 'string', 'max:5000'],
            'next_visit_date' => ['nullable', 'date', 'after_or_equal:today'],
            'follow_up_frequency' => ['nullable', 'string', 'max:100'],
            'follow_up_objective' => ['nullable', 'required_with:next_visit_date,follow_up_frequency', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'],
            ...$this->woundAssessmentRules('details.*', false),
        ];
    }

    public function withValidator($validator): void
    {
        $this->configureWoundAssessmentValidator($validator, 'details', false);
        $validator->after(function ($validator): void {
            $registration = $this->route('registration');
            if (! $registration) {
                $registration = $this->route('scheduledReview')?->registration;
            }
            if (is_string($registration)) {
                $registration = WcRegistration::query()->find($registration);
            }
            if (! $registration instanceof WcRegistration) {
                return;
            }

            $expected = $registration->diagnoses()->pluck('WC_RDIAG_CODE')->sort()->values()->all();
            $submitted = collect($this->input('details', []))->pluck('registration_diagnosis_code')->filter()->values();
            if ($submitted->duplicates()->isNotEmpty() || $submitted->sort()->values()->all() !== $expected) {
                $validator->errors()->add('details', 'يجب إدخال تقييم واحد كامل لكل جرح تابع لهذا التسجيل.');
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'submission_key' => 'مفتاح الإرسال',
            'nurse_code' => 'الممرض السريري',
            'overall_status' => __('clinical.fields.overall_outcome'),
            'details' => 'تقييمات الجروح',
            'details.*.registration_diagnosis_code' => 'هوية الجرح',
            ...$this->woundAssessmentAttributes('details.*'),
        ];
    }
}
