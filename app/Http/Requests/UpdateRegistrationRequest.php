<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRegistrationRequest extends FormRequest
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
            'procedure_code' => ['nullable', 'string', 'size:36', 'exists:wc_procedure_tb,WC_PROC_CODE'],
            'pmh_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom Arabic validation attribute names.
     */
    public function attributes(): array
    {
        return [
            'procedure_code' => 'الإجراء الأولي',
            'pmh_notes' => 'ملاحظات التاريخ المرضي السابق',
        ];
    }
}
