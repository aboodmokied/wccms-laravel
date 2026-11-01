<?php

namespace App\Services;

use App\Models\MrPatient;

class HospitalPatientSyncService implements HospitalPatientSyncServiceInterface
{
    /**
     * Look the patient up in the hospital patients table. Read only: a patient the hospital does not know is
     * reported as not found, never created.
     */
    public function searchAndSyncPatient(string $query): array
    {
        $query = trim($query);

        $patient = MrPatient::where('MR_PAT_ID_NO', $query)->first()
            ?? MrPatient::where('MR_PAT_FULL_NAME', 'LIKE', "%{$query}%")->first();

        if (! $patient) {
            return [
                'found' => false,
                'patient' => null,
                'admission' => null,
            ];
        }

        return [
            'found' => true,
            'patient' => $patient,
            'admission' => [
                'ward_name' => $patient->MR_PAT_HOSPITAL_DEPARTMENT,
                'attending_doctor' => $patient->supervisingDoctor?->PER_EMP_FULL_NAME,
                'admission_date' => $patient->MR_PAT_HOSPITAL_ADMISSION_DATE,
            ],
        ];
    }
}
