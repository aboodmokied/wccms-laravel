<?php

namespace Database\Seeders;

use App\Models\WcMedication;
use Illuminate\Database\Seeder;

class WcMedicationSeeder extends Seeder
{
    public function run(): void
    {
        $medications = [
            'Normal Saline 0.9% Solution',
            'Topical Antibiotic Ointment (Mupirocin)',
            'Silver Sulfadiazine Cream 1%',
            'Enzymatic Debriding Ointment',
            'Povidone-Iodine Antiseptic Wash',
        ];

        foreach ($medications as $name) {
            WcMedication::firstOrCreate(
                ['WC_MED_NAME' => $name],
                ['WC_MED_IS_ACTIVE' => 1]
            );
        }
    }
}
