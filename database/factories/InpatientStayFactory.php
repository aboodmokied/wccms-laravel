<?php

namespace Database\Factories;

use App\Models\InpatientStay;
use App\Support\Hospital;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A ward stay in the local copy of HOS.INPATIENT_ADM_TB (not cancelled and still in the ward by default).
 *
 * @extends Factory<InpatientStay>
 */
class InpatientStayFactory extends Factory
{
    use StoresLocalHospitalRows;

    protected $model = InpatientStay::class;

    public function definition(): array
    {
        return [
            'INPATIENT_CODE' => fake()->unique()->numberBetween(1, 999_999_999),
            'HOS_NO' => Hospital::number(),
            'INPATIENT_DATE' => now()->subDays(3),
            'INPATIENT_SUP_LOC_CD' => null,
            'INPATIENT_RESD_LOC_CD' => null,
            'INPATIENT_ADMISSION_CD' => null,
            'INPATIENT_OUT_DATE' => null,
            'INPATIENT_CANCELED' => config('hospital.admission.not_cancelled_value'),
        ];
    }

    public function forHospital(int $hospitalNo): static
    {
        return $this->state(['HOS_NO' => $hospitalNo]);
    }

    public function cancelled(): static
    {
        return $this->state(['INPATIENT_CANCELED' => 1]);
    }
}
