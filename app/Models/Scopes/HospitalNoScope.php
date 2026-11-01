<?php

namespace App\Models\Scopes;

use App\Support\Hospital;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limits every query of a model to the configured hospital (config hospital.hospital_no).
 *
 * The hospital database serves several hospitals and repeats the same codes in each, so a read without this
 * filter can return another hospital's patient, employee or admission.
 */
class HospitalNoScope implements Scope
{
    public function __construct(private readonly string $column) {}

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->qualifyColumn($this->column), Hospital::number());
    }
}
