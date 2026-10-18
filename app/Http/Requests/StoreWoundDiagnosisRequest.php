<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWoundAssessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWoundDiagnosisRequest extends FormRequest
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
            'clinical_nurse_code' => ['required', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'wct_employee_code' => ['nullable', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'doctor_employee_code' => ['nullable', 'uuid', Rule::exists('per_employee_tb', 'PER_EMP_CODE')],
            'assessment' => ['required', 'array'],
            ...$this->woundAssessmentRules('assessment', true),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('assessment'))) {
            $this->merge(['assessment_collection' => [$this->input('assessment')]]);
        }
    }

    public function withValidator($validator): void
    {
        $this->configureWoundAssessmentValidator($validator, 'assessment_collection', true);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'clinical_nurse_code' => 'الممرض السريري',
            'assessment.wound_type_code' => 'نوع الجرح',
            'assessment.diagnosis_code' => 'التشخيص',
            'assessment.length_mm' => 'طول الجرح',
            'assessment.width_mm' => 'عرض الجرح',
            'assessment.depth_mm' => 'عمق الجرح',
        ];
    }
}
