<?php

namespace Database\Seeders;

use App\Models\WcProcedure;
use Illuminate\Database\Seeder;

class WcProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $procedures = [
            'Sharp Surgical Debridement',
            'Mechanical Cleansing & Irrigation',
            'Negative Pressure Wound Therapy (NPWT)',
            'Standard Wound Dressing Change',
            'Enzymatic Debridement Application',
        ];

        foreach ($procedures as $name) {
            WcProcedure::firstOrCreate(
                ['WC_PROC_NAME' => $name],
                ['WC_PROC_IS_ACTIVE' => 1]
            );
        }
    }
}
