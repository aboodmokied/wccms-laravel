<?php

namespace App\Http\Requests;

use App\Enums\EmployeeRole;
use App\Rules\HospitalEmployeeExists;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'discharging_nurse_code' => ['required', new HospitalEmployeeExists(EmployeeRole::Nurse)],
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
