<?php

namespace App\Models\Concerns;

use App\Exceptions\HospitalLinkException;
use App\Models\Scopes\HospitalNoScope;
use App\Support\Hospital;
use Illuminate\Database\Eloquent\Model;

/**
 * A WCCMS table that points at hospital patients or employees, and so carries the hospital number (HOS_NO).
 *
 * - The HOS_NO column is filled with the configured hospital on create.
 * - Reads are limited to the configured hospital (unless the model sets $scopedToHospital = false).
 * - Before every save, each patient/employee code is checked against the configured hospital. This is the
 *   application-side check that replaces the composite foreign keys when config('hospital.foreign_keys') is off,
 *   and it runs even when they are on.
 *
 * Models using it define:
 *   const HOSPITAL_NO_COLUMN = 'WC_REG_HOS_NO';
 *   protected function hospitalLinks(): array  // ['WC_REG_PATIENT_CD' => MrPatient::class, ...]
 */
trait BelongsToHospital
{
    public static function bootBelongsToHospital(): void
    {
        if (static::isScopedToHospital()) {
            static::addGlobalScope(new HospitalNoScope(static::HOSPITAL_NO_COLUMN));
        }

        // "saving" runs before "creating", so the number is filled here, then checked.
        static::saving(function (Model $model): void {
            $model->{static::HOSPITAL_NO_COLUMN} ??= Hospital::number();
            $model->assertHospitalLinks();
        });
    }

    protected static function isScopedToHospital(): bool
    {
        return ! property_exists(static::class, 'scopedToHospital') || static::$scopedToHospital;
    }

    /**
     * @return array<string, class-string<Model>> link column => hospital model
     */
    abstract protected function hospitalLinks(): array;

    /**
     * Every patient/employee code on this row must exist in the configured hospital.
     *
     * @throws HospitalLinkException
     */
    public function assertHospitalLinks(): void
    {
        $hospitalNo = (int) $this->{static::HOSPITAL_NO_COLUMN};

        if ($hospitalNo !== Hospital::number()) {
            throw new HospitalLinkException(sprintf(
                'السجل يحمل رقم مستشفى %d، والنظام مضبوط على المستشفى %d.',
                $hospitalNo,
                Hospital::number()
            ));
        }

        $codesByModel = [];
        foreach ($this->hospitalLinks() as $column => $modelClass) {
            $code = $this->{$column};
            if ($code !== null && $code !== '' && ($this->isDirty($column) || $this->isDirty(static::HOSPITAL_NO_COLUMN))) {
                $codesByModel[$modelClass][$column] = $code;
            }
        }

        foreach ($codesByModel as $modelClass => $codes) {
            $model = new $modelClass;
            $found = $modelClass::query()
                ->whereIn($model->getKeyName(), array_values(array_unique($codes)))
                ->pluck($model->getKeyName())
                ->map(fn ($code) => (string) $code)
                ->all();

            foreach ($codes as $column => $code) {
                if (! in_array((string) $code, $found, true)) {
                    throw new HospitalLinkException(sprintf(
                        'الرمز %s في %s غير موجود في المستشفى رقم %d.',
                        $code,
                        $column,
                        Hospital::number()
                    ));
                }
            }
        }
    }
}
