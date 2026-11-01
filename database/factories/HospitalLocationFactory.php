<?php

namespace Database\Factories;

use App\Models\HospitalLocation;
use App\Support\Hospital;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A ward or clinic in the local copy of HOS.C_LOCATION_TB.
 *
 * @extends Factory<HospitalLocation>
 */
class HospitalLocationFactory extends Factory
{
    use StoresLocalHospitalRows;

    protected $model = HospitalLocation::class;

    public function definition(): array
    {
        return [
            'LOC_CODE' => fake()->unique()->numberBetween(1, 999),
            'HOS_NO' => Hospital::number(),
            'LOC_NAME_AR' => fake()->randomElement(['الباطنة رجال', 'الجراحة العامة', 'العظام', 'الحروق', 'الباطنة نساء']),
            'LOC_NAME_EN' => null,
        ];
    }

    public function forHospital(int $hospitalNo): static
    {
        return $this->state(['HOS_NO' => $hospitalNo]);
    }
}
