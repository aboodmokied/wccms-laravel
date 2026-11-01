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

    protected function isActive(): Attribute
    {
        return Attribute::get(fn (): bool => (int) $this->EMP_ACTIVE === (int) config('hospital.employees.active_value'));
    }

    /**
     * Employees at work, for the pick lists. History screens keep showing inactive employees through relations.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('EMP_ACTIVE', config('hospital.employees.active_value'));
    }

    /**
     * Employees whose job (EMP_JOB_CD) is one of the role's C_JOB_TB codes; no filter when none are configured.
     */
    public function scopeInRole(Builder $query, EmployeeRole $role): void
    {
        $codes = $role->jobCodes();

        if ($codes !== []) {
            $query->whereIn('EMP_JOB_CD', $codes);
        }
    }

    /**
     * The active employees of the clinic's pick lists, one list per role, read in one query.
     *
     * @return array{nurses: Collection<int, self>, doctors: Collection<int, self>, woundCareTeam: Collection<int, self>}
     */
    public static function pickLists(): array
    {
        $roles = ['nurses' => EmployeeRole::Nurse, 'doctors' => EmployeeRole::Doctor, 'woundCareTeam' => EmployeeRole::WoundCareTeam];
        $anyJob = collect($roles)->contains(fn (EmployeeRole $role): bool => $role->jobCodes() === []);

        $employees = static::query()
            ->active()
            ->when(! $anyJob, fn (Builder $query) => $query->whereIn(
                'EMP_JOB_CD',
                collect($roles)->flatMap(fn (EmployeeRole $role): array => $role->jobCodes())->unique()->values()->all()
            ))
            ->orderedByName()
            ->with('job')
            ->get();

        return array_map(
            fn (EmployeeRole $role): Collection => $employees->filter(fn (self $employee): bool => $role->includes($employee))->values(),
            $roles
        );
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
