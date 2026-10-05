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
        Schema::create('wc_wound_photo_tb', function (Blueprint $table) {
            $table->char('WC_PHOTO_CODE', 36)->primary()->comment('المعرّف الفريد للصورة السريرية (UUID) - المفتاح الأساسي');
            $table->char('WC_PHOTO_REG_DIAGNOSIS_CD', 36)->index()->comment('الجرح/التشخيص الذي تتبع له الصورة (FK -> WC_REG_DIAGNOSIS_TB)');
            $table->char('WC_PHOTO_DAILY_REVIEW_DETAIL_CD', 36)->nullable()->index()->comment('سطر تفاصيل المراجعة اليومية إذا كانت الصورة مأخوذة في مراجعة (FK -> WC_REVIEW_DETAIL_TB)');
            $table->string('WC_PHOTO_STAGE', 20)->index()->comment('مرحلة الصورة السريرية (INITIAL = خط الأساس الأولي / REVIEW = أثناء المراجعات)');
            $table->string('WC_PHOTO_PATH', 255)->comment('مسار التخزين الفرعي للصورة في القرص السريري');
            $table->text('WC_PHOTO_CAPTION')->nullable()->comment('الوصف/التعليق السريري على الصورة');
            $table->dateTime('WC_PHOTO_TAKEN_AT')->comment('تاريخ ووقت التقاط الصورة السريرية');
            $table->char('WC_PHOTO_NURSE_CD', 36)->index()->comment('الممرض/ة المسؤول/ة عن التقاط الصورة (FK -> PER_EMPLOYEE_TB)');
            $table->char('WC_PHOTO_ENTRY_USER_CD', 36)->index()->comment('المستخدم الذي أدخل سجل الصورة في النظام (FK -> PER_USERS_TB)');

            $table->foreign('WC_PHOTO_REG_DIAGNOSIS_CD')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb')->onDelete('cascade');
            $table->foreign('WC_PHOTO_DAILY_REVIEW_DETAIL_CD')->references('WC_REVD_CODE')->on('wc_review_detail_tb')->onDelete('set null');
            $table->foreign('WC_PHOTO_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_PHOTO_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->comment('جدول التوثيق البصري وصور الجروح السريرية عند القبول وأثناء المراجعات الدوريّة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wc_wound_photo_tb');
    }
};
