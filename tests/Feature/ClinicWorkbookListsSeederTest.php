<?php

namespace Tests\Feature;

use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcWoundType;
use Database\Seeders\WcClinicWorkbookListsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicWorkbookListsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_every_workbook_value_once_and_active(): void
    {
        $this->seed(WcClinicWorkbookListsSeeder::class);

        $this->assertSame(['Abrasion', 'Avulsion', 'Crush', 'Laceration', 'Pressure', 'Puncture', 'Surgical', 'blister'], WcWoundType::query()->orderBy('WC_WTYPE_NAME')->pluck('WC_WTYPE_NAME')->sort()->values()->all());
        $this->assertSame(['debridement'], WcProcedure::query()->pluck('WC_PROC_NAME')->all());
        $this->assertSame(['DM', 'HTN', 'Immune Disease', 'Vascular Disease'], WcPmh::query()->pluck('WC_PMH_NAME')->sort()->values()->all());
        $this->assertSame(7, WcDiagnosis::query()->where('WC_DIAG_IS_ACTIVE', 1)->count());
        $this->assertSame(7, WcDressing::query()->where('WC_DRESS_IS_ACTIVE', 1)->count());
        $this->assertSame(16, WcMedication::query()->where('WC_MED_IS_ACTIVE', 1)->count());
        $this->assertSame(13, WcDepartment::query()->where('WC_DEPT_IS_ACTIVE', 1)->count());
    }

    public function test_second_run_adds_nothing(): void
    {
        $this->seed(WcClinicWorkbookListsSeeder::class);

        $this->seed(WcClinicWorkbookListsSeeder::class);

        $this->assertDatabaseCount('wc_diagnosis_tb', 7);
        $this->assertDatabaseCount('wc_wound_type_tb', 8);
        $this->assertDatabaseCount('wc_dressing_tb', 7);
        $this->assertDatabaseCount('wc_medication_tb', 16);
        $this->assertDatabaseCount('wc_procedure_tb', 1);
        $this->assertDatabaseCount('wc_department_tb', 13);
        $this->assertDatabaseCount('wc_pmh_tb', 4);
    }

    public function test_keeps_existing_values_and_their_active_state(): void
    {
        $retired = WcDepartment::create(['WC_DEPT_NAME' => 'CCU', 'WC_DEPT_IS_ACTIVE' => 0]);
        $demo = WcDressing::create(['WC_DRESS_NAME' => 'Sterile Gauze & Tape', 'WC_DRESS_IS_ACTIVE' => 1]);

        $this->seed(WcClinicWorkbookListsSeeder::class);

        $this->assertSame(0, (int) $retired->fresh()->WC_DEPT_IS_ACTIVE);
        $this->assertSame(1, WcDepartment::query()->where('WC_DEPT_NAME', 'CCU')->count());
        $this->assertModelExists($demo);
    }

    public function test_does_not_seed_tissue_states_combinations_or_flags(): void
    {
        $this->seed(WcClinicWorkbookListsSeeder::class);

        foreach (['Sloughing', 'Necrotic', 'black tissue', 'Granulation', 'Pocketing'] as $tissue) {
            $this->assertDatabaseMissing('wc_wound_type_tb', ['WC_WTYPE_NAME' => $tissue]);
        }
        foreach (['swap culture', 'Swape Culture', 'Depredment', 'swap culture, debridement', 'NO', 'YES'] as $procedure) {
            $this->assertDatabaseMissing('wc_procedure_tb', ['WC_PROC_NAME' => $procedure]);
        }
        $this->assertDatabaseMissing('wc_medication_tb', ['WC_MED_NAME' => 'NO']);
        $this->assertDatabaseMissing('wc_pmh_tb', ['WC_PMH_NAME' => 'No']);
    }
}
