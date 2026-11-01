<?php

namespace App\Services;

use App\Models\MrPatient;

interface HospitalPatientSyncServiceInterface
{
    /**
     * Search for a patient in the hospital patients table (read only), returning patient demographics and the
     * hospital admission context.
     *
     * @return array{found: bool, patient: MrPatient|null, admission: array{ward_name: mixed, attending_doctor: mixed, admission_date: mixed}|null}
     */
    public function searchAndSyncPatient(string $query): array;
}
