<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wc_assessment_choice_tb', function (Blueprint $table) {
            $table->char('WC_ACH_CODE', 36)->primary();
            $table->string('WC_ACH_CATEGORY', 40);
            $table->string('WC_ACH_KEY', 50);
            $table->string('WC_ACH_LABEL_AR', 150);
            $table->smallInteger('WC_ACH_IS_ACTIVE')->default(1);
            $table->integer('WC_ACH_DISPLAY_ORDER')->default(0);
            $table->unique(['WC_ACH_CATEGORY', 'WC_ACH_KEY'], 'wc_ach_category_key_unique');
            $table->index(['WC_ACH_CATEGORY', 'WC_ACH_IS_ACTIVE', 'WC_ACH_DISPLAY_ORDER'], 'wc_ach_category_active_order_index');
        });

        Schema::create('wc_registration_pmh_tb', function (Blueprint $table) {
            $table->char('WC_RPMH_CODE', 36)->primary();
            $table->char('WC_RPMH_REGISTRATION_CD', 36);
            $table->char('WC_RPMH_PMH_CD', 36);
            $table->unique(['WC_RPMH_REGISTRATION_CD', 'WC_RPMH_PMH_CD'], 'wc_rpmh_registration_pmh_unique');
            $table->foreign('WC_RPMH_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb')->cascadeOnDelete();
            $table->foreign('WC_RPMH_PMH_CD')->references('WC_PMH_CODE')->on('wc_pmh_tb');
        });

        Schema::create('wc_wound_assessment_tb', function (Blueprint $table) {
            $table->char('WC_WA_CODE', 36)->primary();
            $table->char('WC_WA_REG_DIAGNOSIS_CD', 36);
            $table->char('WC_WA_REVIEW_DETAIL_CD', 36)->nullable();
            $table->string('WC_WA_TYPE', 20);
            $table->char('WC_WA_CLINICAL_EMP_CD', 36);
            $table->char('WC_WA_WCT_EMP_CD', 36)->nullable();
            $table->char('WC_WA_DOCTOR_EMP_CD', 36)->nullable();
            $table->char('WC_WA_ENTRY_USER_CD', 36);
            $table->dateTime('WC_WA_ASSESSED_AT');
            $table->char('WC_WA_WOUND_STATUS_CD', 36);
            $table->char('WC_WA_EXUDATE_AMOUNT_CD', 36);
            $table->char('WC_WA_EXUDATE_TYPE_CD', 36)->nullable();
            $table->char('WC_WA_ODOR_CD', 36);
            $table->char('WC_WA_PAIN_SEVERITY_CD', 36);
            $table->boolean('WC_WA_PAIN_AT_DRESSING');
            $table->char('WC_WA_FEVER_CD', 36)->nullable();
            $table->decimal('WC_WA_LENGTH_MM', 8, 2)->nullable();
            $table->decimal('WC_WA_WIDTH_MM', 8, 2)->nullable();
            $table->decimal('WC_WA_DEPTH_MM', 8, 2)->nullable();
            $table->char('WC_WA_LOCATION_CD', 36)->nullable();
            $table->char('WC_WA_LATERALITY_CD', 36)->nullable();
            $table->string('WC_WA_LOCATION_NOTES', 500)->nullable();
            $table->boolean('WC_WA_SWAB_TAKEN')->nullable();
            $table->char('WC_WA_NUTRITION_CD', 36)->nullable();
            $table->char('WC_WA_DRESSING_CD', 36)->nullable();
            $table->char('WC_WA_MEDICATION_CD', 36)->nullable();
            $table->char('WC_WA_PROCEDURE_CD', 36)->nullable();
            $table->text('WC_WA_TREATMENT_NOTES')->nullable();
            $table->text('WC_WA_CLINICAL_OBSERVATION')->nullable();
            $table->text('WC_WA_WOUND_CHANGES')->nullable();
            $table->boolean('WC_WA_FOLLOW_UP_REQUIRED');

            $table->unique('WC_WA_REVIEW_DETAIL_CD', 'wc_wa_review_detail_unique');
            $table->index(['WC_WA_REG_DIAGNOSIS_CD', 'WC_WA_ASSESSED_AT'], 'wc_wa_diagnosis_assessed_index');
            $table->foreign('WC_WA_REG_DIAGNOSIS_CD')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb')->cascadeOnDelete();
            $table->foreign('WC_WA_REVIEW_DETAIL_CD')->references('WC_REVD_CODE')->on('wc_review_detail_tb')->cascadeOnDelete();
            $table->foreign('WC_WA_CLINICAL_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_WA_WCT_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_WA_DOCTOR_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_WA_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->foreign('WC_WA_WOUND_STATUS_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_EXUDATE_AMOUNT_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_EXUDATE_TYPE_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_ODOR_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_PAIN_SEVERITY_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_FEVER_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_LOCATION_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_LATERALITY_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_NUTRITION_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
            $table->foreign('WC_WA_DRESSING_CD')->references('WC_DRESS_CODE')->on('wc_dressing_tb');
            $table->foreign('WC_WA_MEDICATION_CD')->references('WC_MED_CODE')->on('wc_medication_tb');
            $table->foreign('WC_WA_PROCEDURE_CD')->references('WC_PROC_CODE')->on('wc_procedure_tb');
        });

        Schema::create('wc_assessment_finding_tb', function (Blueprint $table) {
            $table->char('WC_AF_CODE', 36)->primary();
            $table->char('WC_AF_ASSESSMENT_CD', 36);
            $table->char('WC_AF_CHOICE_CD', 36);
            $table->unique(['WC_AF_ASSESSMENT_CD', 'WC_AF_CHOICE_CD'], 'wc_af_assessment_choice_unique');
            $table->foreign('WC_AF_ASSESSMENT_CD')->references('WC_WA_CODE')->on('wc_wound_assessment_tb')->cascadeOnDelete();
            $table->foreign('WC_AF_CHOICE_CD')->references('WC_ACH_CODE')->on('wc_assessment_choice_tb');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wc_assessment_choice_tb ADD CONSTRAINT wc_ach_active_check CHECK (WC_ACH_IS_ACTIVE IN (0, 1))');
            DB::statement("ALTER TABLE wc_wound_assessment_tb ADD CONSTRAINT wc_wa_type_link_check CHECK ((WC_WA_TYPE = 'INITIAL' AND WC_WA_REVIEW_DETAIL_CD IS NULL) OR (WC_WA_TYPE = 'FOLLOW_UP' AND WC_WA_REVIEW_DETAIL_CD IS NOT NULL))");
            DB::statement('ALTER TABLE wc_wound_assessment_tb ADD CONSTRAINT wc_wa_measurement_check CHECK ((WC_WA_LENGTH_MM IS NULL OR (WC_WA_LENGTH_MM > 0 AND WC_WA_LENGTH_MM <= 1000)) AND (WC_WA_WIDTH_MM IS NULL OR (WC_WA_WIDTH_MM > 0 AND WC_WA_WIDTH_MM <= 1000)) AND (WC_WA_DEPTH_MM IS NULL OR (WC_WA_DEPTH_MM >= 0 AND WC_WA_DEPTH_MM <= 1000)))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_assessment_finding_tb');
        Schema::dropIfExists('wc_wound_assessment_tb');
        Schema::dropIfExists('wc_registration_pmh_tb');
        Schema::dropIfExists('wc_assessment_choice_tb');
    }
};
