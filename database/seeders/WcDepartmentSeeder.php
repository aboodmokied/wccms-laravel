<?php

namespace Database\Seeders;

use App\Models\WcDepartment;
use Illuminate\Database\Seeder;

class WcDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Wound Care Clinic',
            'Emergency Department',
            'Outpatient Surgery',
            'Dermatology Clinic',
            'General Surgery',
        ];

        foreach ($departments as $name) {
            WcDepartment::firstOrCreate(
                ['WC_DEPT_NAME' => $name],
                ['WC_DEPT_IS_ACTIVE' => 1]
            );
        }
    }
}
