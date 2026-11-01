<?php

namespace App\Models\Builders;

use App\Exceptions\HospitalTableReadOnly;
use Illuminate\Database\Eloquent\Builder;

/**
 * The query builder of the hospital-table models: reads only. Every write a query can make (insert, update,
 * upsert, increment, delete, truncate, touch) throws, so nothing reaches the database even when model events are
 * skipped (saveQuietly, withoutEvents).
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends Builder<TModel>
 */
class HospitalTableBuilder extends Builder
{
    /**
     * Write methods Eloquent forwards to the base query builder.
     */
    private const FORWARDED_WRITES = [
        'insert', 'insertgetid', 'insertorignore', 'insertorignorereturning', 'insertusing', 'insertorignoreusing',
        'insertreturning', 'updateorinsert', 'updatefrom', 'truncate',
    ];

    public function update(array $values)
    {
        $this->refuse('update');
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->refuse('upsert');
    }

    public function touch($column = null)
    {
        $this->refuse('touch');
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $this->refuse('increment');
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $this->refuse('decrement');
    }

    public function incrementEach(array $columns, array $extra = [])
    {
        $this->refuse('incrementEach');
    }

    public function decrementEach(array $columns, array $extra = [])
    {
        $this->refuse('decrementEach');
    }

    public function delete()
    {
        $this->refuse('delete');
    }

    public function forceDelete()
    {
        $this->refuse('forceDelete');
    }

    public function __call($method, $parameters)
    {
        if (in_array(strtolower($method), self::FORWARDED_WRITES, true)) {
            $this->refuse($method);
        }

        return parent::__call($method, $parameters);
    }

    private function refuse(string $operation): never
    {
        throw HospitalTableReadOnly::for($this->getModel()->getTable(), $operation);
    }
}
