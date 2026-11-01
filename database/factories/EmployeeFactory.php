<?php

namespace Database\Factories;

use App\Enums\EmployeeRole;
use App\Models\Employee;
use App\Support\Hospital;
use Database\Factories\Concerns\StoresLocalHospitalRows;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An employee in the local copy of HOS.EMPLOYEE_TB, active and in the configured hospital by default.
 *
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    use StoresLocalHospitalRows;

    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'EMP_SERIAL' => fake()->unique()->numberBetween(1_000, 999_999_999),
            'HOS_NO' => Hospital::number(),
            'EMP_FIRST_NAME_AR' => fake()->randomElement(['سارة', 'خالد', 'منى', 'رامي', 'هبة', 'محمود']),
            'EMP_FATHER_NAME_AR' => fake()->randomElement(['أحمد', 'علي', 'يوسف', 'حسن']),
            'EMP_GRANDFATHER_NAME_AR' => fake()->randomElement(['محمد', 'سالم', 'عيسى']),
            'EMP_LAST_NAME_AR' => fake()->randomElement(['العلي', 'الأغا', 'شبير', 'الفرا']),
            'EMP_JOB_CD' => null,
            'EMP_ACTIVE' => 1,
            'EMP_REC_CD' => 1,
            'USER_TYPE' => null,
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
            'EMP_FIRST_NAME_AR' => $first,
            'EMP_FATHER_NAME_AR' => $father,
            'EMP_GRANDFATHER_NAME_AR' => $grandfather,
            'EMP_LAST_NAME_AR' => $last,
        ]);
    }

    /**
     * A job of the role's configured C_JOB_TB codes (the first one); no job when the role has none configured.
     */
    public function inRole(EmployeeRole $role): static
    {
        return $this->state(['EMP_JOB_CD' => $role->jobCodes()[0] ?? null]);
    }

    public function doctor(): static
    {
        return $this->inRole(EmployeeRole::Doctor);
    }

    public function nurse(): static
    {
        return $this->inRole(EmployeeRole::Nurse)->state(['USER_TYPE' => config('hospital.employees.nurse_user_type')]);
    }

    public function inactive(): static
    {
        // Left the hospital: the hospital's lists keep EMP_REC_CD = 1 only.
        return $this->state(['EMP_ACTIVE' => 0, 'EMP_REC_CD' => 0]);
    }
}
