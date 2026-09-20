<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasDbFirstAttributes
{
    /**
     * Boot the Database-First trait to automatically assign a UUID string primary key
     * upon model creation if not already set.
     */
    protected static function bootHasDbFirstAttributes(): void
    {
        static::creating(function ($model) {
            $keyName = $model->getKeyName();
            if (empty($model->{$keyName})) {
                $model->{$keyName} = (string) Str::uuid();
            }
        });
    }

    /**
     * Initialize trait to enforce Database-First Eloquent model properties.
     */
    public function initializeHasDbFirstAttributes(): void
    {
        $this->setIncrementing(false);
        $this->setKeyType('string');
        $this->timestamps = false;
    }
}
