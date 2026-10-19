<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WcDepartmentSeeder::class,
            WcPmhSeeder::class,
            WcDiagnosisSeeder::class,
            WcWoundTypeSeeder::class,
            WcDressingSeeder::class,
            WcMedicationSeeder::class,
            WcProcedureSeeder::class,
            WcClinicWorkbookListsSeeder::class,
            WcAssessmentChoiceSeeder::class,
            TestSeeder::class,
        ]);
    }
}
