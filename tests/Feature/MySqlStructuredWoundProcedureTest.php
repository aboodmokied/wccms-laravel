<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySqlStructuredWoundProcedureTest extends TestCase
{
    public function test_required_atomic_workflow_procedures_are_installed_on_mysql(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('يتطلب اتصال MySQL للتحقق من الإجراءات المخزنة.');
        }

        $names = collect(DB::select(
            'SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME IN (?, ?, ?)',
            ['SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS', 'SP_WC_ADD_WOUND_WITH_ASSESSMENT', 'SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS']
        ))->pluck('ROUTINE_NAME')->sort()->values()->all();

        $this->assertSame([
            'SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS',
            'SP_WC_ADD_WOUND_WITH_ASSESSMENT',
            'SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS',
        ], $names);
    }
}
