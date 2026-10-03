<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWoundDiagnosisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wound_type_code' => ['required', 'string', 'size:36', 'exists:wc_wound_type_tb,WC_WTYPE_CODE'],
            'diagnosis_code' => ['required', 'string', 'size:36', 'exists:wc_diagnosis_tb,WC_DIAG_CODE'],
            'dressing_code' => ['nullable', 'string', 'size:36', 'exists:wc_dressing_tb,WC_DRESS_CODE'],
            'medication_code' => ['nullable', 'string', 'size:36', 'exists:wc_medication_tb,WC_MED_CODE'],
            'clinical_nurse_code' => ['required', 'string', 'size:36', 'exists:per_employee_tb,PER_EMP_CODE'],
        ];
    }

    /**
     * Custom Arabic validation attribute names.
     */
    public function attributes(): array
    {
        return [
            'wound_type_code' => 'نوع الجرح',
            'diagnosis_code' => 'التشخيص السريري',
            'dressing_code' => 'الضمادة الأولية',
            'medication_code' => 'العلاج الموضعي الأولي',
            'clinical_nurse_code' => 'الممرض الفاحص/السريري',
        ];
    }
}
