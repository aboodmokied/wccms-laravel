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
        // 1. User Accounts
        Schema::create('per_users_tb', function (Blueprint $table) {
            $table->char('PER_USER_CODE', 36)->primary();
            $table->string('PER_USER_USERNAME', 100)->unique();
            $table->string('PER_USER_PASSWORD', 255);
            $table->string('PER_USER_ROLE', 20); // NURSE, ADMIN
            $table->smallInteger('PER_USER_IS_ACTIVE')->default(1);
        });

        // 2. Employee Directory
        Schema::create('per_employee_tb', function (Blueprint $table) {
            $table->char('PER_EMP_CODE', 36)->primary();
            $table->string('PER_EMP_FULL_NAME', 150);
            $table->string('PER_EMP_JOB_TITLE', 100)->nullable();
            $table->char('PER_EMP_USER_CD', 36)->nullable()->index();

            $table->foreign('PER_EMP_USER_CD')
                ->references('PER_USER_CODE')
                ->on('per_users_tb')
                ->nullOnDelete();
        });

        // 3. Patient Master Index
        Schema::create('mr_patient_tb', function (Blueprint $table) {
            $table->char('MR_PAT_CODE', 36)->primary();
            $table->string('MR_PAT_ID_NO', 50)->unique();
            $table->string('MR_PAT_FULL_NAME', 150);
            $table->string('MR_PAT_PHONE', 30)->nullable();
            $table->string('MR_PAT_GENDER', 10);
            $table->date('MR_PAT_DOB')->nullable();
            $table->dateTime('MR_PAT_LAST_SYNCED_AT')->nullable();
        });

        // 4. Master Reference Tables (7 tables)
        Schema::create('wc_department_tb', function (Blueprint $table) {
            $table->char('WC_DEPT_CODE', 36)->primary();
            $table->string('WC_DEPT_NAME', 100);
            $table->smallInteger('WC_DEPT_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_pmh_tb', function (Blueprint $table) {
            $table->char('WC_PMH_CODE', 36)->primary();
            $table->string('WC_PMH_NAME', 100);
            $table->smallInteger('WC_PMH_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_diagnosis_tb', function (Blueprint $table) {
            $table->char('WC_DIAG_CODE', 36)->primary();
            $table->string('WC_DIAG_NAME', 100);
            $table->smallInteger('WC_DIAG_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_wound_type_tb', function (Blueprint $table) {
            $table->char('WC_WTYPE_CODE', 36)->primary();
            $table->string('WC_WTYPE_NAME', 100);
            $table->smallInteger('WC_WTYPE_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_dressing_tb', function (Blueprint $table) {
            $table->char('WC_DRESS_CODE', 36)->primary();
            $table->string('WC_DRESS_NAME', 100);
            $table->smallInteger('WC_DRESS_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_medication_tb', function (Blueprint $table) {
            $table->char('WC_MED_CODE', 36)->primary();
            $table->string('WC_MED_NAME', 100);
            $table->smallInteger('WC_MED_IS_ACTIVE')->default(1);
        });

        Schema::create('wc_procedure_tb', function (Blueprint $table) {
            $table->char('WC_PROC_CODE', 36)->primary();
            $table->string('WC_PROC_NAME', 100);
            $table->smallInteger('WC_PROC_IS_ACTIVE')->default(1);
        });

        // 5. Registration Requests
        Schema::create('wc_request_reg_tb', function (Blueprint $table) {
            $table->char('WC_REQ_CODE', 36)->primary();
            $table->char('WC_REQ_PATIENT_CD', 36)->index();
            $table->string('WC_REQ_PAT_NAME_SNAPSHOT', 150);
            $table->string('WC_REQ_PAT_ID_SNAPSHOT', 50);
            $table->string('WC_REQ_PAT_PHONE_SNAPSHOT', 30)->nullable();
            $table->char('WC_REQ_NURSE_CD', 36)->index();
            $table->char('WC_REQ_ENTRY_USER_CD', 36)->index();
            $table->char('WC_REQ_REVIEWED_BY_CD', 36)->nullable()->index();
            $table->string('WC_REQ_STATUS', 20)->default('PENDING')->index();
            $table->text('WC_REQ_NOTES')->nullable();
            $table->dateTime('WC_REQ_CREATED_AT');

            $table->foreign('WC_REQ_PATIENT_CD')->references('MR_PAT_CODE')->on('mr_patient_tb');
            $table->foreign('WC_REQ_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_REQ_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->foreign('WC_REQ_REVIEWED_BY_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
        });

        // 6. Clinical Registration (1:1 merged model)
        Schema::create('wc_registration_tb', function (Blueprint $table) {
            $table->char('WC_REG_CODE', 36)->primary();
            $table->char('WC_REG_REQUEST_CD', 36)->index();
            $table->char('WC_REG_PATIENT_CD', 36)->index();
            $table->char('WC_REG_DEPARTMENT_CD', 36)->index();
            $table->char('WC_REG_PROCEDURE_CD', 36)->nullable()->index();
            $table->text('WC_REG_PMH_NOTES')->nullable();
            $table->dateTime('WC_REG_DATE');
            $table->char('WC_REG_NURSE_CD', 36)->index();
            $table->char('WC_REG_ENTRY_USER_CD', 36)->index();
            $table->string('WC_REG_STATUS', 20)->default('ACTIVE')->index();
            $table->dateTime('WC_REG_DISCHARGED_AT')->nullable();
            $table->char('WC_REG_DISCHARGED_BY_EMP_CD', 36)->nullable()->index();

            $table->foreign('WC_REG_REQUEST_CD')->references('WC_REQ_CODE')->on('wc_request_reg_tb');
            $table->foreign('WC_REG_PATIENT_CD')->references('MR_PAT_CODE')->on('mr_patient_tb');
            $table->foreign('WC_REG_DEPARTMENT_CD')->references('WC_DEPT_CODE')->on('wc_department_tb');
            $table->foreign('WC_REG_PROCEDURE_CD')->references('WC_PROC_CODE')->on('wc_procedure_tb');
            $table->foreign('WC_REG_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_REG_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
            $table->foreign('WC_REG_DISCHARGED_BY_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
        });

        // 7. Registration Diagnoses
        Schema::create('wc_reg_diagnosis_tb', function (Blueprint $table) {
            $table->char('WC_RDIAG_CODE', 36)->primary();
            $table->char('WC_RDIAG_REGISTRATION_CD', 36)->index();
            $table->char('WC_RDIAG_DIAGNOSIS_CD', 36)->index();
            $table->char('WC_RDIAG_WOUND_TYPE_CD', 36)->index();
            $table->char('WC_RDIAG_CLINICAL_EMP_CD', 36)->index();
            $table->char('WC_RDIAG_ENTRY_USER_CD', 36)->index();

            $table->foreign('WC_RDIAG_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_RDIAG_DIAGNOSIS_CD')->references('WC_DIAG_CODE')->on('wc_diagnosis_tb');
            $table->foreign('WC_RDIAG_WOUND_TYPE_CD')->references('WC_WTYPE_CODE')->on('wc_wound_type_tb');
            $table->foreign('WC_RDIAG_CLINICAL_EMP_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_RDIAG_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
        });

        // 8. Scheduled Reviews
        Schema::create('wc_scheduled_review_tb', function (Blueprint $table) {
            $table->char('WC_SREV_CODE', 36)->primary();
            $table->char('WC_SREV_REGISTRATION_CD', 36)->index();
            $table->dateTime('WC_SREV_SCHEDULED_DATE');
            $table->integer('WC_SREV_VISIT_SERIAL')->default(1);
            $table->string('WC_SREV_STATUS', 20)->default('PENDING')->index(); // PENDING, COMPLETED, MISSED, CANCELLED, VOIDED

            $table->foreign('WC_SREV_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
        });

        // 9. Daily Review Visit Header
        Schema::create('wc_daily_review_tb', function (Blueprint $table) {
            $table->char('WC_DREV_CODE', 36)->primary();
            $table->char('WC_DREV_REGISTRATION_CD', 36)->index();
            $table->char('WC_DREV_SCHEDULED_REVIEW_CD', 36)->nullable()->index();
            $table->dateTime('WC_DREV_VISIT_DATE');
            $table->char('WC_DREV_NURSE_CD', 36)->index();
            $table->char('WC_DREV_ENTRY_USER_CD', 36)->index();
            $table->text('WC_DREV_GENERAL_NOTES')->nullable();

            $table->foreign('WC_DREV_REGISTRATION_CD')->references('WC_REG_CODE')->on('wc_registration_tb');
            $table->foreign('WC_DREV_SCHEDULED_REVIEW_CD')->references('WC_SREV_CODE')->on('wc_scheduled_review_tb');
            $table->foreign('WC_DREV_NURSE_CD')->references('PER_EMP_CODE')->on('per_employee_tb');
            $table->foreign('WC_DREV_ENTRY_USER_CD')->references('PER_USER_CODE')->on('per_users_tb');
        });

        // 10. Daily Review Detail
        Schema::create('wc_daily_review_detail_tb', function (Blueprint $table) {
            $table->char('WC_DREVD_CODE', 36)->primary();
            $table->char('WC_DREVD_DAILY_REVIEW_CD', 36)->index();
            $table->char('WC_DREVD_REG_DIAGNOSIS_CD', 36)->index();
            $table->char('WC_DREVD_DRESSING_CD', 36)->nullable()->index();
            $table->char('WC_DREVD_MEDICATION_CD', 36)->nullable()->index();
            $table->text('WC_DREVD_OBSERVATION')->nullable();
            $table->string('WC_DREVD_OUTCOME_STATUS', 30); // IMPROVED_DISCHARGE, IMPROVED_FOLLOWUP, WORSENED, DECEASED

            $table->foreign('WC_DREVD_DAILY_REVIEW_CD')->references('WC_DREV_CODE')->on('wc_daily_review_tb');
            $table->foreign('WC_DREVD_REG_DIAGNOSIS_CD')->references('WC_RDIAG_CODE')->on('wc_reg_diagnosis_tb');
            $table->foreign('WC_DREVD_DRESSING_CD')->references('WC_DRESS_CODE')->on('wc_dressing_tb');
            $table->foreign('WC_DREVD_MEDICATION_CD')->references('WC_MED_CODE')->on('wc_medication_tb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wc_daily_review_detail_tb');
        Schema::dropIfExists('wc_daily_review_tb');
        Schema::dropIfExists('wc_scheduled_review_tb');
        Schema::dropIfExists('wc_reg_diagnosis_tb');
        Schema::dropIfExists('wc_registration_tb');
        Schema::dropIfExists('wc_request_reg_tb');
        Schema::dropIfExists('wc_procedure_tb');
        Schema::dropIfExists('wc_medication_tb');
        Schema::dropIfExists('wc_dressing_tb');
        Schema::dropIfExists('wc_wound_type_tb');
        Schema::dropIfExists('wc_diagnosis_tb');
        Schema::dropIfExists('wc_pmh_tb');
        Schema::dropIfExists('wc_department_tb');
        Schema::dropIfExists('mr_patient_tb');
        Schema::dropIfExists('per_employee_tb');
        Schema::dropIfExists('per_users_tb');
    }
};
