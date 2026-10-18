<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wc_registration_tb', function (Blueprint $table) {
            $table->boolean('WC_REG_HAS_ALLERGIES')->nullable();
            $table->text('WC_REG_ALLERGIES_DESCRIPTION')->nullable();
            $table->boolean('WC_REG_NO_COMORBIDITIES')->nullable();
            $table->dateTime('WC_REG_NEXT_VISIT_DATE')->nullable();
            $table->string('WC_REG_FOLLOW_UP_FREQUENCY', 100)->nullable();
            $table->text('WC_REG_FOLLOW_UP_OBJECTIVE')->nullable();
        });

        Schema::table('wc_daily_review_tb', function (Blueprint $table) {
            $table->char('WC_DREV_SUBMISSION_KEY', 36)->nullable()->unique('wc_drev_submission_key_unique');
            $table->unique('WC_DREV_SCHEDULED_REVIEW_CD', 'wc_drev_scheduled_review_unique');
        });

        Schema::table('wc_wound_photo_tb', function (Blueprint $table) {
            $table->char('WC_PHOTO_ASSESSMENT_CD', 36)->nullable()->index();
            $table->foreign('WC_PHOTO_ASSESSMENT_CD')->references('WC_WA_CODE')->on('wc_wound_assessment_tb')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wc_registration_tb ADD CONSTRAINT wc_reg_allergy_context_check CHECK ((WC_REG_HAS_ALLERGIES IS NULL) OR (WC_REG_HAS_ALLERGIES = 1 AND NULLIF(TRIM(WC_REG_ALLERGIES_DESCRIPTION), '') IS NOT NULL) OR (WC_REG_HAS_ALLERGIES = 0 AND WC_REG_ALLERGIES_DESCRIPTION IS NULL))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wc_registration_tb DROP CHECK wc_reg_allergy_context_check');
        }

        Schema::table('wc_wound_photo_tb', function (Blueprint $table) {
            $table->dropForeign(['WC_PHOTO_ASSESSMENT_CD']);
            $table->dropColumn('WC_PHOTO_ASSESSMENT_CD');
        });

        Schema::table('wc_daily_review_tb', function (Blueprint $table) {
            $table->dropUnique('wc_drev_scheduled_review_unique');
            $table->dropUnique('wc_drev_submission_key_unique');
            $table->dropColumn('WC_DREV_SUBMISSION_KEY');
        });

        Schema::table('wc_registration_tb', function (Blueprint $table) {
            $table->dropColumn([
                'WC_REG_HAS_ALLERGIES',
                'WC_REG_ALLERGIES_DESCRIPTION',
                'WC_REG_NO_COMORBIDITIES',
                'WC_REG_NEXT_VISIT_DATE',
                'WC_REG_FOLLOW_UP_FREQUENCY',
                'WC_REG_FOLLOW_UP_OBJECTIVE',
            ]);
        });
    }
};
