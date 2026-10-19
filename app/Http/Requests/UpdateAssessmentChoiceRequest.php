<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentChoiceRequest extends FormRequest
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
            'label_en' => ['required', 'string', 'max:150'],
            'label_ar' => ['required', 'string', 'max:150'],
            'display_order' => ['required', 'integer', 'min:0', 'max:32767'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'label_en' => 'الاسم الإنجليزي',
            'label_ar' => 'التسمية العربية',
            'display_order' => 'الترتيب',
        ];
    }
}
