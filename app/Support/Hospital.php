<?php

namespace App\Support;

/**
 * Reads the hospital settings of config/hospital.php.
 */
class Hospital
{
    /**
     * The HOS_NO of the hospital WCCMS serves.
     */
    public static function number(): int
    {
        return (int) config('hospital.hospital_no');
    }

    /**
     * A hospital table name, prefixed with the hospital schema when one is configured (HOS in production).
     */
    public static function table(string $key): string
    {
        $table = config("hospital.tables.{$key}");
        $schema = config('hospital.schema');

        return filled($schema) ? "{$schema}.{$table}" : $table;
    }

    /**
     * True when WCCMS reads the real hospital database, where it must never write to the hospital tables.
     */
    public static function isExternal(): bool
    {
        return filled(config('hospital.schema'));
    }
}
