<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReviewRequest extends FormRequest
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
            'nurse_code' => ['required', 'string', 'size:36', 'exists:per_employee_tb,PER_EMP_CODE'],
            'overall_status' => ['required', 'string', 'in:IMPROVED_DISCHARGE,IMPROVED_SCHEDULED,WORSENED_STAY,DEATH'],
            'general_notes' => ['nullable', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_review_code' => ['nullable', 'string', 'size:36', 'exists:wc_scheduled_review_tb,WC_SREV_CODE'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.diagnosis_code' => ['required', 'string', 'size:36', 'exists:wc_reg_diagnosis_tb,WC_RDIAG_CODE'],
            'details.*.dressing_code' => ['nullable', 'string', 'size:36', 'exists:wc_dressing_tb,WC_DRESS_CODE'],
            'details.*.medication_code' => ['nullable', 'string', 'size:36', 'exists:wc_medication_tb,WC_MED_CODE'],
            'details.*.observation' => ['nullable', 'string'],
            'details.*.outcome_status' => ['required', 'string', 'in:IMPROVED_DISCHARGE,IMPROVED_FOLLOWUP,WORSENED,DECEASED'],
            'details.*.wound_photos' => ['nullable', 'array'],
            'details.*.wound_photos.*' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Custom Arabic validation attribute names.
     */
    public function attributes(): array
    {
        return [
            'nurse_code' => 'الممرض المباشر / السريري',
            'overall_status' => 'النتيجة الإجمالية للزيارة',
            'general_notes' => 'الملاحظات العامة',
            'scheduled_date' => 'تاريخ الموعد المجدول',
            'details' => 'تفاصيل تقييم الجروح',
            'details.*.diagnosis_code' => 'تشخيص الجرح',
            'details.*.dressing_code' => 'الضمادة المطبقة',
            'details.*.medication_code' => 'العلاج الموضعي',
            'details.*.observation' => 'الملاحظة السريرية',
            'details.*.outcome_status' => 'تقييم حالة الجرح',
            'details.*.wound_photos' => 'صور الجرح المرفقة',
            'details.*.wound_photos.*' => 'صورة المراجعة للجرح',
        ];
    }
}
