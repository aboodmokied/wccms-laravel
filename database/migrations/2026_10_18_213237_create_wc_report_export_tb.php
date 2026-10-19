<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wc_report_export_tb', function (Blueprint $table) {
            $table->char('WC_RPEX_CODE', 36)->primary()->comment('المعرّف الفريد لعملية تصدير التقرير (UUID) - المفتاح الأساسي');
            $table->char('WC_RPEX_USER_CD', 36)->index()->comment('المستخدم الذي صدّر التقرير (FK -> PER_USERS_TB)');
            $table->dateTime('WC_RPEX_EXPORTED_AT')->index()->comment('تاريخ ووقت التصدير');
            $table->date('WC_RPEX_PERIOD_FROM')->comment('أول يوم من الشهر الأول في فترة التقرير');
            $table->date('WC_RPEX_PERIOD_TO')->comment('أول يوم من الشهر الأخير في فترة التقرير');
            $table->text('WC_RPEX_DEPARTMENT_CODES')->nullable()->comment('رموز الأقسام المحددة (JSON)، فارغ = كل الأقسام');
            $table->integer('WC_RPEX_CASE_COUNT')->comment('عدد الحالات في ورقة الحالات');

            $table->foreign('WC_RPEX_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wc_report_export_tb');
    }
};
