<?php

namespace App\Enums;

use App\Models\Employee;
use App\Support\HospitalCode;

/**
 * The role an employee field asks for, matched on EMPLOYEE_TB.EMP_JOB_CD against the C_JOB_TB codes configured in
 * hospital.employees.job_codes. A role with no codes configured does not filter.
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
        if ($this === self::WoundCareTeam) {
            $doctors = self::Doctor->jobCodes();
            $nurses = self::Nurse->jobCodes();

            // The team is doctors and nurses: until both are known, any job.
            return $doctors === [] || $nurses === [] ? [] : array_values(array_unique([...$doctors, ...$nurses]));
        }

        return self::parseCodes(config("hospital.employees.job_codes.{$this->value}"));
    }

    public function includes(Employee $employee): bool
    {
        $codes = $this->jobCodes();

        return $codes === [] || in_array((int) $employee->EMP_JOB_CD, $codes, true);
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

    /**
     * @return list<int>
     */
    private static function parseCodes(mixed $codes): array
    {
        $values = is_array($codes) ? $codes : explode(',', (string) $codes);

        return array_values(array_unique(array_filter(
            array_map(fn ($code): ?int => HospitalCode::parse(is_string($code) ? trim($code) : $code), $values)
        )));
    }
}
