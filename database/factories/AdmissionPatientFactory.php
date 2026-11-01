<?php

namespace Database\Factories;

use App\Models\AdmissionPatient;
use App\Support\Hospital;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A hospital admission in the local copy of HOS.ADMISSION_PATIENT_TB. Give it the patient, ward and doctor codes of
 * the same hospital; an admission is current only with a not-cancelled stay (InpatientStayFactory).
 *
 * @extends Factory<AdmissionPatient>
 */
class AdmissionPatientFactory extends Factory
{
    use StoresLocalHospitalRows;

    protected $model = AdmissionPatient::class;

    public function definition(): array
    {
        return [
            'ADMISSION_CODE' => fake()->unique()->numberBetween(1, 999_999_999),
            'HOS_NO' => Hospital::number(),
            'ADMISSION_PATREC_CD' => 0,
            'ADMISSION_LOC_CD' => null,
            'ADMISSION_ENTER_DATE' => now()->subDays(3),
            'ADMISSION_ENTER_DOC_CD' => null,
            'ADMISSION_OUT_DATE' => null,
        ];
    }

    public function forHospital(int $hospitalNo): static
    {
        return $this->state(['HOS_NO' => $hospitalNo]);
    }

    public function discharged(): static
    {
        return $this->state(['ADMISSION_OUT_DATE' => now()->subDay()]);
    }
}
