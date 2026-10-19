<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 014: assessment choices are medical terms and are displayed in English. Adds the English name and fills it
 * for every seeded choice; this map is a snapshot and is not changed after the migration has run.
 */
return new class extends Migration
{
    private const ENGLISH_LABELS = [
        'WOUND_STATUS' => ['FIRST_VISIT' => 'First Visit', 'DETERIORATING' => 'Deteriorating', 'STATIC' => 'Static', 'IMPROVING' => 'Improving'],
        'WOUND_BED' => ['GRANULATION' => 'Granulation', 'NECROTIC' => 'Necrotic', 'SLOUGHING' => 'Sloughing', 'EPITHELIALIZING' => 'Epithelializing'],
        'WOUND_MARGIN' => ['NONE_OBSERVED' => 'None Observed', 'ERYTHEMA' => 'Erythema', 'EDEMA' => 'Edema', 'DELAYED_HEALING' => 'Delayed Healing', 'POCKETING' => 'Pocketing'],
        'EXUDATE_AMOUNT' => ['DRY' => 'Dry', 'LOW' => 'Low', 'MEDIUM' => 'Moderate', 'HIGH' => 'High'],
        'EXUDATE_TYPE' => ['WATERY' => 'Watery', 'CLOUDY' => 'Cloudy', 'PURULENT' => 'Purulent', 'CLEAR' => 'Clear'],
        'ODOR' => ['NONE' => 'None', 'SLIGHT' => 'Slight', 'MODERATE' => 'Moderate', 'OFFENSIVE' => 'Offensive'],
        'PAIN_SEVERITY' => ['NONE' => 'None', 'MODERATE' => 'Moderate', 'SEVERE' => 'Severe'],
        'FEVER' => ['NONE' => 'None', 'LOW' => 'Low-grade', 'HIGH' => 'High'],
        'NUTRITION' => ['WELL_NOURISHED' => 'Well Nourished', 'MALNOURISHED' => 'Malnourished'],
        'LOCATION' => ['HEAD' => 'Head', 'ABDOMEN' => 'Abdomen', 'BACK' => 'Back', 'LEG' => 'Leg', 'BUTTOCKS' => 'Buttocks', 'FOOT' => 'Foot', 'HAND' => 'Hand', 'FOREARM' => 'Forearm', 'OTHER' => 'Other'],
        'LATERALITY' => ['NOT_APPLICABLE' => 'Not Applicable', 'LEFT' => 'Left', 'RIGHT' => 'Right', 'BILATERAL' => 'Bilateral'],
    ];

    public function up(): void
    {
        Schema::table('wc_assessment_choice_tb', function (Blueprint $table) {
            $table->string('WC_ACH_LABEL_EN', 150)->nullable()->after('WC_ACH_LABEL_AR')
                ->comment('الاسم الإنجليزي المعروض للخيار (المصطلحات الطبية تُعرض بالإنجليزية)');
        });

        foreach (self::ENGLISH_LABELS as $category => $labels) {
            foreach ($labels as $key => $label) {
                DB::table('wc_assessment_choice_tb')
                    ->where('WC_ACH_CATEGORY', $category)
                    ->where('WC_ACH_KEY', $key)
                    ->whereNull('WC_ACH_LABEL_EN')
                    ->update(['WC_ACH_LABEL_EN' => $label]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('wc_assessment_choice_tb', function (Blueprint $table) {
            $table->dropColumn('WC_ACH_LABEL_EN');
        });
    }
};
