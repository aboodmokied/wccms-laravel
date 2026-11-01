<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWoundAssessment;
use App\Models\PerEmployee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptRegistrationRequest extends FormRequest
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
            'department_code' => ['required', 'uuid', Rule::exists('wc_department_tb', 'WC_DEPT_CODE')->where('WC_DEPT_IS_ACTIVE', 1)],
            'procedure_code' => ['nullable', 'uuid', Rule::exists('wc_procedure_tb', 'WC_PROC_CODE')->where('WC_PROC_IS_ACTIVE', 1)],
            'clinic_nurse_code' => ['required', 'string', Rule::exists(PerEmployee::class, 'PER_EMP_CODE')],
            'wct_employee_code' => ['nullable', 'string', Rule::exists(PerEmployee::class, 'PER_EMP_CODE')],
            'doctor_employee_code' => ['nullable', 'string', Rule::exists(PerEmployee::class, 'PER_EMP_CODE')],
            'has_allergies' => ['required', 'boolean'],
            'allergies_description' => ['exclude_unless:has_allergies,1', 'required_if:has_allergies,1', 'nullable', 'string', 'max:2000'],
            'no_comorbidities' => ['required', 'boolean'],
            'pmh_codes' => ['exclude_if:no_comorbidities,1', 'nullable', 'array'],
            'pmh_codes.*' => ['required', 'uuid', 'distinct', Rule::exists('wc_pmh_tb', 'WC_PMH_CODE')->where('WC_PMH_IS_ACTIVE', 1)],
            'pmh_notes' => ['nullable', 'string', 'max:2000'],
            'next_visit_date' => ['nullable', 'date', 'after_or_equal:today', 'required_without:follow_up_frequency'],
            'follow_up_frequency' => ['nullable', 'string', 'max:100', 'required_without:next_visit_date'],
            'follow_up_objective' => ['required_with:next_visit_date,follow_up_frequency', 'string', 'max:2000'],
            'diagnoses' => ['required', 'array', 'min:1'],
            ...$this->woundAssessmentRules('diagnoses.*', true),
        ];
    }

    public function withValidator($validator): void
    {
        $this->configureWoundAssessmentValidator($validator, 'diagnoses', true);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'department_code' => 'قسم العيادة',
            'clinic_nurse_code' => 'الممرض السريري',
            'wct_employee_code' => 'عضو فريق الجروح',
            'doctor_employee_code' => 'الطبيب',
            'has_allergies' => __('clinical.fields.allergies'),
            'allergies_description' => __('clinical.fields.allergies_description'),
            'no_comorbidities' => __('clinical.fields.pmh'),
            'pmh_codes' => __('clinical.fields.pmh'),
            'pmh_notes' => __('clinical.fields.pmh_notes'),
            'next_visit_date' => 'تاريخ الزيارة القادمة',
            'follow_up_frequency' => 'تكرار المتابعة',
            'follow_up_objective' => 'هدف المتابعة',
            'diagnoses' => 'الجروح',
            ...$this->woundAssessmentAttributes('diagnoses.*'),
        ];
    }
}
