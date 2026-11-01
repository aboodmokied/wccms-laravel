<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A WCCMS row points at a patient or employee that does not exist in the configured hospital, or carries another
 * hospital's number. Raised before anything is written.
 */
class HospitalLinkException extends RuntimeException
{
    //
}
