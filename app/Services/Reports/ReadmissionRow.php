<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;

/**
 * A case whose patient already had an earlier registration, with that earlier registration's facts.
 */
final readonly class ReadmissionRow
{
    public function __construct(
        public CaseRow $case,
        public CarbonImmutable $previousRegisteredAt,
        public CaseOutcome $previousOutcome,
    ) {}
}
