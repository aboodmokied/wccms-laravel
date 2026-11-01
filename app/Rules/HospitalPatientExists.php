<?php

namespace App\Rules;

use App\Models\MrPatient;
use App\Support\HospitalCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The value is an MRP_PATREC_CODE in the configured hospital (HOSPITAL_NO).
 *
 * Laravel's "exists" rule queries the table directly and skips the model's HOS_NO scope, so it would accept a
 * patient of another hospital; this rule goes through App\Models\MrPatient and its scope instead.
 */
class HospitalPatientExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = HospitalCode::parse($value);

        if ($code === null || ! MrPatient::query()->whereKey($code)->exists()) {
            $fail('المريض المحدد في :attribute غير موجود في هذا المستشفى.');
        }
    }
}
