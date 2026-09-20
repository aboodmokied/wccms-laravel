<?php

namespace Tests\Feature;

use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcWoundType;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_master_reference_tables_seed_cleanly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, WcDepartment::count());
        $this->assertGreaterThan(0, WcPmh::count());
        $this->assertGreaterThan(0, WcDiagnosis::count());
        $this->assertGreaterThan(0, WcWoundType::count());
        $this->assertGreaterThan(0, WcDressing::count());
        $this->assertGreaterThan(0, WcMedication::count());
        $this->assertGreaterThan(0, WcProcedure::count());
    }
}
