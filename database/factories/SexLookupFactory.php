<?php

namespace Database\Factories;

use App\Models\SexLookup;
use App\Support\Hospital;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A row of the local copy of HOS.C_SEX_TB. The codes are test fixtures only: WCCMS never assumes which code is male;
 * it classifies by SEX_NAME_EN.
 *
 * @extends Factory<SexLookup>
 */
class SexLookupFactory extends Factory
{
    use StoresLocalHospitalRows;

    public const FEMALE_CODE = 1;

    public const MALE_CODE = 2;

    protected $model = SexLookup::class;

    public function definition(): array
    {
        return [
            'SEX_CODE' => self::MALE_CODE,
            'HOS_NO' => Hospital::number(),
            'SEX_NAME_AR' => 'ذكر',
            'SEX_NAME_EN' => 'Male',
        ];
    }

    public function female(): static
    {
        return $this->state(['SEX_CODE' => self::FEMALE_CODE, 'SEX_NAME_AR' => 'انثى', 'SEX_NAME_EN' => 'Female']);
    }

    public function forHospital(int $hospitalNo): static
    {
        return $this->state(['HOS_NO' => $hospitalNo]);
    }
}
