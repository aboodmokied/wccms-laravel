<?php

namespace App\Services;

interface HospitalPatientSyncServiceInterface
{
    /**
     * Search for a patient in the hospital patient master index or local database,
     * returning patient demographics and current hospital admission context.
     */
    public function searchAndSyncPatient(string $query): array;
}
