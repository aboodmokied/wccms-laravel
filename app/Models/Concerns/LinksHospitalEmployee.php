<?php

namespace App\Models\Concerns;

use App\Models\Employee;
use App\Support\Hospital;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The account-to-employee link of wc_users_tb: (PER_USER_EMP_CD, PER_USER_HOS_NO) -> EMPLOYEE_TB (EMP_SERIAL, HOS_NO).
 *
 * Accounts are not filtered by hospital (the login finds an account by username whatever its hospital), so the link
 * itself is: an account linked to an employee of another hospital resolves to no employee here, never to this
 * hospital's employee that happens to share the serial.
 */
trait LinksHospitalEmployee
{
    use BelongsToHospital;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'linked_employee_code', 'EMP_SERIAL');
    }

    /**
     * PER_USER_EMP_CD when the link belongs to the configured hospital, otherwise null. Read by the employee relation
     * (lazy and eager loading both read the foreign key through the model's attributes).
     */
    protected function linkedEmployeeCode(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->PER_USER_EMP_CD !== null
            && (int) $this->PER_USER_HOS_NO === Hospital::number()
                ? (int) $this->PER_USER_EMP_CD
                : null);
    }

    /**
     * @return array<string, class-string<Model>>
     */
    protected function hospitalLinks(): array
    {
        return ['PER_USER_EMP_CD' => Employee::class];
    }
}
