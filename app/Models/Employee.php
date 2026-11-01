<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Models\Concerns\HospitalTable;
use App\Support\Hospital;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An employee of the hospital system: HOS.EMPLOYEE_TB, key (EMP_SERIAL, HOS_NO). Read only.
 */
class Employee extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'employees';

    /**
     * The four Arabic name parts, in display order.
     */
    public const NAME_COLUMNS = ['EMP_FIRST_NAME_AR', 'EMP_FATHER_NAME_AR', 'EMP_GRANDFATHER_NAME_AR', 'EMP_LAST_NAME_AR'];

    protected $primaryKey = 'EMP_SERIAL';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'EMP_SERIAL' => 'integer',
        ];
    }

    /**
     * Full Arabic name joined from the four name columns.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => collect(self::NAME_COLUMNS)
            ->map(fn (string $column) => trim((string) $this->{$column}))
            ->filter()
            ->implode(' '));
    }

    /**
     * Arabic job name from C_JOB_TB.
     */
    protected function jobTitle(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->job?->JOB_NAME_AR);
    }

    /**
     * At work by the same rule as scopeActive().
     */
    protected function isActive(): Attribute
    {
        return Attribute::get(function (): bool {
            $active = self::activeValue();
            $record = self::recordStatusForAllLists();

            return ($active === null || (int) $this->EMP_ACTIVE === $active)
                && ($record === null || (int) $this->EMP_REC_CD === $record);
        });
    }

    /**
     * Employees at work, for the pick lists: EMP_ACTIVE = hospital.employees.active_value, plus
     * EMP_REC_CD = hospital.employees.record_status when HOSPITAL_EMP_REC_CD_LISTS=all (the doctor role always adds
     * it). History screens keep showing inactive employees through relations.
     */
    public function scopeActive(Builder $query): void
    {
        if (($active = self::activeValue()) !== null) {
            $query->where('EMP_ACTIVE', $active);
        }

        if (($record = self::recordStatusForAllLists()) !== null) {
            $query->where('EMP_REC_CD', $record);
        }
    }

    /**
     * Employees of the role: its C_JOB_TB jobs, and for doctors EMP_REC_CD (App\Enums\EmployeeRole).
     */
    public function scopeInRole(Builder $query, EmployeeRole $role): void
    {
        $role->constrain($query);
    }

    /**
     * The active employees of the clinic's pick lists, one list per role, read in one query, and the labels of the
     * roles whose job codes are unknown (their lists hold every active employee; the forms say so).
     *
     * @return array{nurses: Collection<int, self>, doctors: Collection<int, self>, woundCareTeam: Collection<int, self>, unfilteredRoles: list<string>}
     */
    public static function pickLists(): array
    {
        $roles = ['nurses' => EmployeeRole::Nurse, 'doctors' => EmployeeRole::Doctor, 'woundCareTeam' => EmployeeRole::WoundCareTeam];

        $employees = static::query()
            ->active()
            ->inRole(EmployeeRole::WoundCareTeam)
            ->orderedByName()
            ->with('job')
            ->get();

        return [
            ...array_map(
                fn (EmployeeRole $role): Collection => $employees->filter(fn (self $employee): bool => $role->includes($employee))->values(),
                $roles
            ),
            'unfilteredRoles' => self::unfilteredRoleLabels(...array_values($roles)),
        ];
    }

    /**
     * @return list<string>
     */
    public static function unfilteredRoleLabels(EmployeeRole ...$roles): array
    {
        return array_values(array_map(
            fn (EmployeeRole $role): string => $role->label(),
            array_filter($roles, fn (EmployeeRole $role): bool => $role->isUnfiltered())
        ));
    }

    private static function activeValue(): ?int
    {
        $value = config('hospital.employees.active_value');

        return $value === null || $value === '' ? null : (int) $value;
    }

    private static function recordStatusForAllLists(): ?int
    {
        $value = config('hospital.employees.record_status');

        return $value === null || $value === '' || config('hospital.employees.record_status_lists') !== 'all'
            ? null
            : (int) $value;
    }

    public function scopeOrderedByName(Builder $query): void
    {
        foreach (self::NAME_COLUMNS as $column) {
            $query->orderBy($column);
        }
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobLookup::class, 'EMP_JOB_CD', 'JOB_CODE');
    }

    /**
     * The WCCMS account linked to this employee; the link lives on WC_USERS_TB.PER_USER_EMP_CD.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'PER_USER_EMP_CD', 'EMP_SERIAL')
            ->where('PER_USER_HOS_NO', Hospital::number());
    }
}
