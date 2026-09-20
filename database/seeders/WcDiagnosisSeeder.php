<?php

namespace Database\Seeders;

use App\Models\WcDiagnosis;
use Illuminate\Database\Seeder;

class WcDiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        $diagnoses = [
            'Diabetic Foot Ulcer',
            'Pressure Injury Stage III/IV',
            'Venous Leg Ulcer',
            'Surgical Site Infection',
            'Ischemic Arterial Ulcer',
        ];

        foreach ($diagnoses as $name) {
            WcDiagnosis::firstOrCreate(
                ['WC_DIAG_NAME' => $name],
                ['WC_DIAG_IS_ACTIVE' => 1]
            );
        }
    }
}
