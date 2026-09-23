<?php

namespace App\Services;

use App\Models\MrPatient;
use Illuminate\Support\Str;

class HospitalPatientSyncService implements HospitalPatientSyncServiceInterface
{
    public function searchAndSyncPatient(string $query): array
    {
        $query = trim($query);

        // 1. Check local patient index first by ID number or Name
        $patient = MrPatient::where('MR_PAT_ID_NO', $query)
            ->orWhere('MR_PAT_FULL_NAME', 'LIKE', "%{$query}%")
            ->first();

        // 2. If not found locally, simulate sync from external hospital master index
        if (! $patient) {
            $isIdSearch = preg_match('/^[0-9]+$/', $query);

            $idNo = $isIdSearch ? $query : '40'.rand(1000000, 9999999);
            $fullName = $isIdSearch ? 'مريض جديد محول من المستشفى' : $query;

            $patient = MrPatient::create([
                'MR_PAT_CODE' => (string) Str::uuid(),
                'MR_PAT_ID_NO' => $idNo,
                'MR_PAT_FULL_NAME' => $fullName,
                'MR_PAT_PHONE' => '059'.rand(1000000, 9999999),
                'MR_PAT_GENDER' => 'MALE',
                'MR_PAT_DOB' => '1988-06-15',
                'MR_PAT_LAST_SYNCED_AT' => now(),
            ]);
        }

        // 3. Mock Admission Context from hospital ward system
        $admissionContext = [
            'ward_name' => 'قسم الجراحة - مجمع الشفاء الطبي',
            'attending_doctor' => 'د. أحمد محمود',
            'admission_date' => now()->subDays(3)->format('Y-m-d'),
        ];

        return [
            'found' => true,
            'patient' => $patient,
            'admission' => $admissionContext,
        ];
    }
}
