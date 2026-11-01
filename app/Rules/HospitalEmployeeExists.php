<?php

namespace App\Rules;

use App\Enums\EmployeeRole;
use App\Models\Employee;
use App\Support\HospitalCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The value is an EMP_SERIAL in the configured hospital (HOSPITAL_NO), and, when a role is given, the employee's
 * job is one of that role's C_JOB_TB codes (the same filter as the field's pick list).
 *
 * Laravel's "exists" rule queries the table directly and skips the model's HOS_NO scope, so it would accept an
 * employee of another hospital; this rule goes through App\Models\Employee and its scope instead.
 */
class HospitalEmployeeExists implements ValidationRule
{
    public function __construct(private readonly ?EmployeeRole $role = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = HospitalCode::parse($value);
        $employee = $code === null ? null : Employee::query()->find($code);

        if ($employee === null) {
            $fail('الموظف المحدد في :attribute غير موجود في هذا المستشفى.');

            return;
        }

        if ($this->role !== null && ! $this->role->includes($employee)) {
            $fail('الموظف المحدد في :attribute ليس من '.$this->role->label().'.');
        }
    }
}
