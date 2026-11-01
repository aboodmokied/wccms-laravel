<?php

namespace App\Enums;

use App\Models\Employee;
use App\Support\StaffJobs;
use Illuminate\Database\Eloquent\Builder;

/**
 * The role an employee field asks for:
 * - job: EMPLOYEE_TB.EMP_JOB_CD among the role's C_JOB_TB codes (App\Support\StaffJobs);
 * - doctors always need EMP_REC_CD = hospital.employees.record_status, as the hospital's own doctor lists do; with
 *   HOSPITAL_EMP_REC_CD_LISTS=all (the default) Employee::scopeActive applies it to every list.
 * A role whose codes are unknown does not filter on the job; Employee::pickLists() reports it on the screen.
 * The wound care team is doctors and nurses.
 */
enum EmployeeRole: string
{
    case Doctor = 'doctors';
    case Nurse = 'nurses';
    case WoundCareTeam = 'wound_care_team';

    /**
     * The job codes of this role; empty means "any job".
     *
     * @return list<int>
     */
    public function jobCodes(): array
    {
        return match ($this) {
            self::Doctor => StaffJobs::doctorCodes(),
            self::Nurse => StaffJobs::nurseCodes(),
            self::WoundCareTeam => self::Doctor->jobCodes() === [] || self::Nurse->jobCodes() === []
                ? []
                : array_values(array_unique([...self::Doctor->jobCodes(), ...self::Nurse->jobCodes()])),
        };
    }

    /**
     * True when the role's job codes are unknown, so its list holds every active employee.
     */
    public function isUnfiltered(): bool
    {
        return $this->jobCodes() === [];
    }

    /**
     * The EMP_REC_CD value this role requires, or null.
     */
    public function recordStatus(): ?int
    {
        $value = config('hospital.employees.record_status');

        if ($value === null || $value === '' || $this !== self::Doctor) {
            return null;
        }

        return (int) $value;
    }

    public function constrain(Builder $query): void
    {
        if ($this === self::WoundCareTeam) {
            if (self::Doctor->hasConditions() && self::Nurse->hasConditions()) {
                $query->where(fn (Builder $team) => $team
                    ->where(fn (Builder $doctors) => self::Doctor->constrain($doctors))
                    ->orWhere(fn (Builder $nurses) => self::Nurse->constrain($nurses)));
            }

            return;
        }

        if ($this->jobCodes() !== []) {
            $query->whereIn('EMP_JOB_CD', $this->jobCodes());
        }

        if ($this->recordStatus() !== null) {
            $query->where('EMP_REC_CD', $this->recordStatus());
        }
    }

    public function includes(Employee $employee): bool
    {
        if ($this === self::WoundCareTeam) {
            return ! (self::Doctor->hasConditions() && self::Nurse->hasConditions())
                || self::Doctor->includes($employee)
                || self::Nurse->includes($employee);
        }

        $codes = $this->jobCodes();

        return ($codes === [] || in_array((int) $employee->EMP_JOB_CD, $codes, true))
            && ($this->recordStatus() === null || (int) $employee->EMP_REC_CD === $this->recordStatus());
    }

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'الأطباء',
            self::Nurse => 'الممرضين',
            self::WoundCareTeam => 'فريق الجروح',
        };
    }

    /**
     * The .env variable that sets the codes (none for the wound care team, which is doctors and nurses).
     */
    public function envVariable(): ?string
    {
        return match ($this) {
            self::Doctor => 'HOSPITAL_DOCTOR_JOB_CODES',
            self::Nurse => 'HOSPITAL_NURSE_JOB_CODES',
            self::WoundCareTeam => null,
        };
    }

    private function hasConditions(): bool
    {
        return $this->jobCodes() !== [] || $this->recordStatus() !== null;
    }
}
