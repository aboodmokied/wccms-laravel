<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcceptRegistrationRequest extends FormRequest
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
            'department_code' => ['nullable', 'string', 'size:36', 'exists:wc_department_tb,WC_DEPT_CODE'],
            'procedure_code' => ['nullable', 'string', 'size:36', 'exists:wc_procedure_tb,WC_PROC_CODE'],
            'pmh_notes' => ['nullable', 'string', 'max:2000'],
            'clinic_nurse_code' => ['nullable', 'string', 'size:36', 'exists:per_employee_tb,PER_EMP_CODE'],
            'wound_type_code' => ['nullable', 'string', 'size:36', 'exists:wc_wound_type_tb,WC_WTYPE_CODE'],
            'diagnosis_code' => ['nullable', 'string', 'size:36', 'exists:wc_diagnosis_tb,WC_DIAG_CODE'],
            'medication_code' => ['nullable', 'string', 'size:36', 'exists:wc_medication_tb,WC_MED_CODE'],
            'diagnoses' => ['nullable', 'array'],
            'diagnoses.*.wound_type_code' => ['nullable', 'string', 'size:36', 'exists:wc_wound_type_tb,WC_WTYPE_CODE'],
            'diagnoses.*.diagnosis_code' => ['nullable', 'string', 'size:36', 'exists:wc_diagnosis_tb,WC_DIAG_CODE'],
            'diagnoses.*.medication_code' => ['nullable', 'string', 'size:36', 'exists:wc_medication_tb,WC_MED_CODE'],
            'wound_photos' => ['nullable', 'array'],
            'wound_photos.*' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'diagnoses.*.wound_photos' => ['nullable', 'array'],
            'diagnoses.*.wound_photos.*' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Custom Arabic attribute names.
     */
    public function attributes(): array
    {
        return [
            'department_code' => 'قسم العيادة',
            'procedure_code' => 'الإجراء الطبي الأولي',
            'pmh_notes' => 'التاريخ المرضي والملاحظات',
            'clinic_nurse_code' => 'الممرض المعالج/الفاحص',
            'wound_type_code' => 'نوع الجرح',
            'diagnosis_code' => 'التشخيص الطبي الأولي',
            'medication_code' => 'العلاج الدوائي الأولي',
            'diagnoses' => 'التشخيصات السريرية',
            'diagnoses.*.wound_type_code' => 'نوع الجرح الفرعي',
            'diagnoses.*.diagnosis_code' => 'التشخيص الطبي الفرعي',
            'diagnoses.*.medication_code' => 'العلاج الدوائي الفرعي',
            'wound_photos' => 'صور الجرح الأولي',
            'wound_photos.*' => 'صورة الجرح الأولي',
            'diagnoses.*.wound_photos' => 'صور الجرح الفرعي',
            'diagnoses.*.wound_photos.*' => 'صورة الجرح الفرعي',
        ];
    }
}
