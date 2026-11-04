<?php

namespace Tests\Feature;

use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcWoundType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Settings open with no sign-in or role: the hospital decides who reaches WCCMS.
     */
    public function test_master_data_admin_routes_open_without_sign_in(): void
    {
        $response = $this->get('/admin/departments');

        $response->assertOk();
    }

    /**
     * US1 (T006): Admin can access master data index pages and search items.
     */
    public function test_admin_can_view_master_data_index_pages(): void
    {
        $department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم الجراحة العامة',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $response = $this->call(
            'GET',
            '/admin/departments',
            ['search' => 'الجراحة'],
        );

        $response->assertOk();
        $response->assertSee('قسم الجراحة العامة');
        $response->assertSee('الأقسام');
    }

    /**
     * US2 (T015): Admin can create a new master data item with UUID primary key.
     */
    public function test_admin_can_create_new_master_data_item(): void
    {
        $response = $this->call(
            'POST',
            '/admin/medications',
            ['name' => 'مرهم السيلفر نترات الإضافي'],
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wc_medication_tb', [
            'WC_MED_NAME' => 'مرهم السيلفر نترات الإضافي',
            'WC_MED_IS_ACTIVE' => 1,
        ]);
    }

    /**
     * US2 (T015): Duplicate item name is rejected with validation error.
     */
    public function test_duplicate_item_name_is_rejected(): void
    {
        WcDressing::create([
            'WC_DRESS_CODE' => (string) Str::uuid(),
            'WC_DRESS_NAME' => 'ضمادة رغوية ممتازة',
            'WC_DRESS_IS_ACTIVE' => 1,
        ]);

        $response = $this->call(
            'POST',
            '/admin/dressings',
            ['name' => 'ضمادة رغوية ممتازة'],
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * US2 (T015): Admin can update item name.
     */
    public function test_admin_can_update_master_data_item_name(): void
    {
        $procedure = WcProcedure::create([
            'WC_PROC_CODE' => (string) Str::uuid(),
            'WC_PROC_NAME' => 'تنظيف جرح قديم',
            'WC_PROC_IS_ACTIVE' => 1,
        ]);

        $response = $this->call(
            'PUT',
            "/admin/procedures/{$procedure->WC_PROC_CODE}",
            ['name' => 'تنظيف وتنضير الجرح السريري'],
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wc_procedure_tb', [
            'WC_PROC_CODE' => $procedure->WC_PROC_CODE,
            'WC_PROC_NAME' => 'تنظيف وتنضير الجرح السريري',
        ]);
    }

    /**
     * US3 (T017): Admin can toggle item active status (IS_ACTIVE 1 <-> 0) without hard delete.
     */
    public function test_admin_can_toggle_item_active_status_without_delete(): void
    {
        $woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'قرحة الفراش المزمنة',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        // Toggle to inactive (0)
        $response = $this->call(
            'PATCH',
            "/admin/wound-types/{$woundType->WC_WTYPE_CODE}/toggle",
            [],
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_wound_type_tb', [
            'WC_WTYPE_CODE' => $woundType->WC_WTYPE_CODE,
            'WC_WTYPE_IS_ACTIVE' => 0,
        ]);

        // Toggle back to active (1)
        $response2 = $this->call(
            'PATCH',
            "/admin/wound-types/{$woundType->WC_WTYPE_CODE}/toggle",
            [],
        );

        $response2->assertRedirect();

        $this->assertDatabaseHas('wc_wound_type_tb', [
            'WC_WTYPE_CODE' => $woundType->WC_WTYPE_CODE,
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);
    }

    /**
     * US1 & US3: Test index listing and toggling across PMH and Diagnoses domains.
     */
    public function test_admin_can_manage_pmh_and_diagnoses_domains(): void
    {
        $pmh = WcPmh::create([
            'WC_PMH_CODE' => (string) Str::uuid(),
            'WC_PMH_NAME' => 'ارتفاع ضغط الدم الشرياني',
            'WC_PMH_IS_ACTIVE' => 1,
        ]);

        $diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'قرحة الشرايين الطرفية',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        $response1 = $this->get('/admin/pmh');
        $response1->assertOk();
        $response1->assertSee('ارتفاع ضغط الدم الشرياني');

        $response2 = $this->get('/admin/diagnoses');
        $response2->assertOk();
        $response2->assertSee('قرحة الشرايين الطرفية');
    }
}
