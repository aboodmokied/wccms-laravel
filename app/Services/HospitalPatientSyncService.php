<?php

namespace App\Services;

use App\Models\MrPatient;

class HospitalPatientSyncService implements HospitalPatientSyncServiceInterface
{
    /**
     * Look the patient up in the hospital patients table (MR_PATIENT_TB, configured hospital only). Read only: a
     * patient the hospital does not know is reported as not found, never created.
     *
     * A number is tried first as the document number (MRP_ID) or medical record number (MRP_PATREC_CODE); otherwise
     * the words are matched against the four Arabic name columns.
     */
    public function searchAndSyncPatient(string $query): array
    {
        $query = trim($query);
        $relations = ['sex', 'currentAdmission.currentStay.ward', 'currentAdmission.location', 'currentAdmission.doctor'];

        $patient = MrPatient::query()->with($relations)->byNumber($query)->first()
            ?? MrPatient::query()->with($relations)->matching($query)->orderedByName()->first();

        if (! $patient) {
            return [
                'found' => false,
                'patient' => null,
                'admission' => null,
            ];
        }

        $admission = $patient->currentAdmission;

        return [
            'found' => true,
            'patient' => $patient,
            'admission' => [
                'ward_name' => $patient->hospital_ward_name,
                'attending_doctor' => $admission?->doctor?->full_name,
                'admission_date' => $admission?->ADMISSION_ENTER_DATE?->toDateString(),
            ],
        ];
    }
}
