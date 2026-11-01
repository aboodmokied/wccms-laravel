<?php

namespace App\Http\Requests;

use App\Enums\EmployeeRole;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Rules\HospitalEmployeeExists;
use App\Rules\HospitalPatientExists;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_code' => [
                'required',
                new HospitalPatientExists,
                function ($attribute, $value, $fail) {
                    $hasPendingRequest = WcRequestReg::where('WC_REQ_PATIENT_CD', $value)
                        ->whereIn('WC_REQ_STATUS', ['PENDING', 'VISIT_IN_PROGRESS'])
                        ->exists();

                    if ($hasPendingRequest) {
                        $fail(__('ui.requests.errors.has_pending_request', ['default' => 'المريض لديه طلب تسجيل نشط حالياً أو قيد الانتظار.']));

                        return;
                    }

                    $hasActiveRegistration = WcRegistration::where('WC_REG_PATIENT_CD', $value)
                        ->where('WC_REG_STATUS', 'ACTIVE')
                        ->exists();

                    if ($hasActiveRegistration) {
                        $fail(__('ui.requests.errors.has_active_registration', ['default' => 'تنبيه: هذا المريض لديه تسجيل نشط حالياً في العيادة.']));
                    }
                },
            ],
            'attending_nurse_code' => ['required', new HospitalEmployeeExists(EmployeeRole::Nurse)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
