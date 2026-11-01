<?php

namespace Database\Factories;

use App\Models\MrPatient;
use App\Models\SexLookup;
use App\Support\Hospital;
use App\Support\LocalHospitalCopy;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A patient in the local copy of HOS.MR_PATIENT_TB, by default in the configured hospital (HOSPITAL_NO).
 *
 * @extends Factory<MrPatient>
 */
class MrPatientFactory extends Factory
{
    use StoresLocalHospitalRows;

    protected $model = MrPatient::class;

    public function definition(): array
    {
        return [
            'MRP_PATREC_CODE' => fake()->unique()->numberBetween(100_000, 999_999_999),
            'HOS_NO' => Hospital::number(),
            'MRP_ID_TYPE_CD' => 1,
            'MRP_ID' => fake()->unique()->numberBetween(400_000_000, 999_999_999),
            'MRP_FIRST_NAME_AR' => fake()->randomElement(['محمد', 'أحمد', 'خالد', 'يوسف', 'سامي', 'فاطمة', 'مريم', 'آمنة']),
            'MRP_FATHER_NAME_AR' => fake()->randomElement(['عبدالله', 'إبراهيم', 'حسن', 'محمود']),
            'MRP_GRANDFATHER_NAME_AR' => fake()->randomElement(['سليم', 'عمر', 'علي', 'صالح']),
            'MRP_LAST_NAME_AR' => fake()->randomElement(['النجار', 'الشوا', 'حلس', 'أبو شعبان', 'الريس']),
            'MRP_DOB' => fake()->dateTimeBetween('-85 years', '-18 years')->format('Y-m-d'),
            'MRP_MOBILE_NO' => '059'.fake()->numerify('#######'),
            'MRP_SEX_CD' => null,
        ];
    }

    public function forHospital(int $hospitalNo): static
    {
        return $this->state(['HOS_NO' => $hospitalNo]);
    }

    /**
     * The four Arabic name columns: first, father, grandfather, last.
     */
    public function named(?string $first, ?string $father = null, ?string $grandfather = null, ?string $last = null): static
    {
        return $this->state([
            'MRP_FIRST_NAME_AR' => $first,
            'MRP_FATHER_NAME_AR' => $father,
            'MRP_GRANDFATHER_NAME_AR' => $grandfather,
            'MRP_LAST_NAME_AR' => $last,
        ]);
    }

    public function male(): static
    {
        return $this->withSex(SexLookupFactory::MALE_CODE, 'ذكر', 'Male');
    }

    public function female(): static
    {
        return $this->withSex(SexLookupFactory::FEMALE_CODE, 'انثى', 'Female');
    }

    /**
     * Uses (or creates) the C_SEX_TB row in the patient's hospital.
     */
    public function withSex(int $code, ?string $nameAr, ?string $nameEn): static
    {
        return $this->state(function (array $attributes) use ($code, $nameAr, $nameEn): array {
            $hospitalNo = (int) ($attributes['HOS_NO'] ?? Hospital::number());

            LocalHospitalCopy::firstOrInsert(
                SexLookup::class,
                ['SEX_CODE' => $code, 'HOS_NO' => $hospitalNo],
                ['SEX_NAME_AR' => $nameAr, 'SEX_NAME_EN' => $nameEn],
            );

            return ['MRP_SEX_CD' => $code];
        });
    }
}
