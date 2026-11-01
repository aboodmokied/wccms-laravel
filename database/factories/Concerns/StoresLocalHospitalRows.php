<?php

namespace Database\Factories\Concerns;

use App\Support\LocalHospitalCopy;
use Illuminate\Support\Collection;

/**
 * For factories of the read-only hospital-table models: create() inserts into the local copy through
 * App\Support\LocalHospitalCopy instead of saving the model.
 */
trait StoresLocalHospitalRows
{
    protected function store(Collection $results)
    {
        $results->each(function ($model): void {
            if (! isset($this->connection)) {
                $model->setConnection($model->newQueryWithoutScopes()->getConnection()->getName());
            }

            LocalHospitalCopy::insert($model);

            $this->createChildren($model);
        });
    }
}
