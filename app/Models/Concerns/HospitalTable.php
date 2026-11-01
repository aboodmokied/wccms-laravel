<?php

namespace App\Models\Concerns;

use App\Exceptions\HospitalTableReadOnly;
use App\Models\Builders\HospitalTableBuilder;
use App\Models\Scopes\HospitalNoScope;
use App\Support\Hospital;

/**
 * A table of the hospital system (schema HOS), read directly.
 *
 * - The table name comes from config/hospital.php, prefixed with the hospital schema in production.
 * - Every query is limited to the configured hospital: the real key is (code, HOS_NO) and the same code exists in
 *   several hospitals, so the model's key plus this filter identify one row.
 * - Read only, in every environment: saving or deleting a model throws, and so does every write its query builder
 *   could make. The local copies used in development and tests are filled through App\Support\LocalHospitalCopy.
 *
 * Models using it define HOSPITAL_TABLE (a key of config('hospital.tables')).
 */
trait HospitalTable
{
    public static function bootHospitalTable(): void
    {
        static::addGlobalScope(new HospitalNoScope('HOS_NO'));

        static::saving(function ($model): never {
            throw HospitalTableReadOnly::for($model->getTable(), $model->exists ? 'update' : 'insert');
        });

        static::deleting(function ($model): never {
            throw HospitalTableReadOnly::for($model->getTable(), 'delete');
        });
    }

    public function initializeHospitalTable(): void
    {
        $this->timestamps = false;
        $this->incrementing = false;
        $this->keyType = 'int';
    }

    public function getTable(): string
    {
        return Hospital::table(static::HOSPITAL_TABLE);
    }

    public function newEloquentBuilder($query): HospitalTableBuilder
    {
        return new HospitalTableBuilder($query);
    }

    /**
     * refresh() and fresh() run without global scopes, so the key alone would find this code in any hospital.
     */
    protected function setKeysForSelectQuery($query)
    {
        return parent::setKeysForSelectQuery($query)->where('HOS_NO', '=', $this->getAttribute('HOS_NO') ?? Hospital::number());
    }
}
