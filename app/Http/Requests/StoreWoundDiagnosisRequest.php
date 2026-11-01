<?php

namespace App\Http\Requests;

use App\Enums\EmployeeRole;
use App\Http\Requests\Concerns\ValidatesWoundAssessment;
use App\Rules\HospitalEmployeeExists;
use Illuminate\Foundation\Http\FormRequest;

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
            'clinical_nurse_code' => ['required', new HospitalEmployeeExists(EmployeeRole::Nurse)],
            'wct_employee_code' => ['nullable', new HospitalEmployeeExists(EmployeeRole::WoundCareTeam)],
            'doctor_employee_code' => ['nullable', new HospitalEmployeeExists(EmployeeRole::Doctor)],
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
            ...$this->woundAssessmentAttributes('assessment'),
        ];
    }
}
