<?php

namespace App\Support;

/**
 * Hospital codes (MRP_PATREC_CODE, EMP_SERIAL) are NUMBER(9). Only digit strings or integers of up to 9 digits are
 * accepted, so nothing non-numeric ever reaches an Oracle NUMBER column (ORA-01722).
 */
final class HospitalCode
{
    public static function parse(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 && $value <= 999_999_999 ? $value : null;
        }

        if (! is_string($value) || ! preg_match('/^\d{1,9}$/', $value)) {
            return null;
        }

        return (int) $value > 0 ? (int) $value : null;
    }
}
