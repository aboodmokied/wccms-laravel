<?php

namespace App\Exceptions;

use LogicException;

/**
 * Something tried to insert, update or delete a hospital-system table through its model. WCCMS only reads them.
 */
class HospitalTableReadOnly extends LogicException
{
    public static function for(string $table, string $operation): self
    {
        return new self("WCCMS only reads the hospital table {$table}: {$operation} is not allowed.");
    }
}
