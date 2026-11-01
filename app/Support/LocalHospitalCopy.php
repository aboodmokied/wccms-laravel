<?php

namespace App\Support;

use App\Exceptions\HospitalTableReadOnly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Fills the local copies of the hospital tables that the migration builds for development and tests (factories,
 * seeders, the development login). The hospital-table models are read only, so rows go in through the query
 * builder here. Refuses whenever HOSPITAL_SCHEMA points at the real hospital tables.
 */
final class LocalHospitalCopy
{
    /**
     * Inserts the row unless one with the same key exists in that hospital (HOS_NO defaults to HOSPITAL_NO), and
     * returns it read through its model. An existing row is returned as it is, never updated.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<string, mixed>  $key
     * @param  array<string, mixed>  $values
     * @return TModel
     */
    public static function firstOrInsert(string $modelClass, array $key, array $values = []): Model
    {
        $model = new $modelClass;
        $key = ['HOS_NO' => $values['HOS_NO'] ?? Hospital::number(), ...$key];

        if (! self::table($model)->where($key)->exists()) {
            self::table($model)->insert([...$values, ...$key]);
        }

        return $modelClass::query()->withoutGlobalScopes()->where($key)->firstOrFail();
    }

    /**
     * Inserts a model built in memory (a factory's make()) and marks it as stored.
     */
    public static function insert(Model $model): void
    {
        self::table($model)->insert($model->getAttributes());

        $model->exists = true;
        $model->wasRecentlyCreated = true;
        $model->syncOriginal();
    }

    private static function table(Model $model): Builder
    {
        if (Hospital::isExternal()) {
            throw HospitalTableReadOnly::for($model->getTable(), 'filling a local copy');
        }

        return $model->getConnection()->table($model->getTable());
    }
}
