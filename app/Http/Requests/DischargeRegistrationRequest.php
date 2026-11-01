<?php

namespace App\Http\Requests;

use App\Models\PerEmployee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DischargeRegistrationRequest extends FormRequest
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
            'discharging_nurse_code' => ['required', 'string', Rule::exists(PerEmployee::class, 'PER_EMP_CODE')],
        ];
    }

    /**
     * Custom Arabic validation attribute names.
     */
    public function attributes(): array
    {
        return [
            'discharging_nurse_code' => 'الممرض المنفذ للتخريج',
        ];
    }
}
