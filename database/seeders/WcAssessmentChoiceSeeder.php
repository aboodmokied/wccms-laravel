<?php

namespace Database\Seeders;

use App\Models\WcAssessmentChoice;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class WcAssessmentChoiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->choices() as $category => $choices) {
            foreach ($choices as $order => [$key, $label, $englishLabel]) {
                WcAssessmentChoice::query()->firstOrCreate(
                    ['WC_ACH_CATEGORY' => $category, 'WC_ACH_KEY' => $key],
                    [
                        'WC_ACH_CODE' => Uuid::uuid5(Uuid::NAMESPACE_URL, "wccms:assessment-choice:{$category}:{$key}")->toString(),
                        'WC_ACH_LABEL_AR' => $label,
                        'WC_ACH_LABEL_EN' => $englishLabel,
                        'WC_ACH_IS_ACTIVE' => 1,
                        'WC_ACH_DISPLAY_ORDER' => $order + 1,
                    ]
                );
            }
        }
    }

    /**
     * Stable key, Arabic label, English display label (spec 014: medical terms are shown in English).
     *
     * @return array<string, list<array{string, string, string}>>
     */
    private function choices(): array
    {
        return [
            'WOUND_STATUS' => [['FIRST_VISIT', 'الزيارة الأولى', 'First Visit'], ['DETERIORATING', 'متدهور', 'Deteriorating'], ['STATIC', 'ثابت', 'Static'], ['IMPROVING', 'يتحسن', 'Improving']],
            'WOUND_BED' => [['GRANULATION', 'نسيج حبيبي', 'Granulation'], ['NECROTIC', 'نسيج نخري', 'Necrotic'], ['SLOUGHING', 'نسيج رخو متموت', 'Sloughing'], ['EPITHELIALIZING', 'تكوّن ظهارة', 'Epithelializing']],
            'WOUND_MARGIN' => [['NONE_OBSERVED', 'لا توجد علامة مسجلة', 'None Observed'], ['ERYTHEMA', 'احمرار', 'Erythema'], ['EDEMA', 'وذمة', 'Edema'], ['DELAYED_HEALING', 'تأخر الالتئام', 'Delayed Healing'], ['POCKETING', 'جيوب', 'Pocketing']],
            'EXUDATE_AMOUNT' => [['DRY', 'جاف', 'Dry'], ['LOW', 'قليل', 'Low'], ['MEDIUM', 'متوسط', 'Moderate'], ['HIGH', 'غزير', 'High']],
            'EXUDATE_TYPE' => [['WATERY', 'مائي', 'Watery'], ['CLOUDY', 'عكر', 'Cloudy'], ['PURULENT', 'صديدي', 'Purulent'], ['CLEAR', 'صافي', 'Clear']],
            'ODOR' => [['NONE', 'لا توجد رائحة', 'None'], ['SLIGHT', 'خفيفة', 'Slight'], ['MODERATE', 'متوسطة', 'Moderate'], ['OFFENSIVE', 'كريهة', 'Offensive']],
            'PAIN_SEVERITY' => [['NONE', 'لا يوجد ألم', 'None'], ['MODERATE', 'متوسط', 'Moderate'], ['SEVERE', 'شديد', 'Severe']],
            'FEVER' => [['NONE', 'لا توجد حرارة', 'None'], ['LOW', 'حرارة بسيطة', 'Low-grade'], ['HIGH', 'حرارة مرتفعة', 'High']],
            'NUTRITION' => [['WELL_NOURISHED', 'جيد التغذية', 'Well Nourished'], ['MALNOURISHED', 'سوء تغذية', 'Malnourished']],
            'LOCATION' => [['HEAD', 'الرأس', 'Head'], ['ABDOMEN', 'البطن', 'Abdomen'], ['BACK', 'الظهر', 'Back'], ['LEG', 'الساق', 'Leg'], ['BUTTOCKS', 'الأرداف', 'Buttocks'], ['FOOT', 'القدم', 'Foot'], ['HAND', 'اليد', 'Hand'], ['FOREARM', 'الساعد', 'Forearm'], ['OTHER', 'أخرى', 'Other']],
            'LATERALITY' => [['NOT_APPLICABLE', 'لا ينطبق', 'Not Applicable'], ['LEFT', 'يسار', 'Left'], ['RIGHT', 'يمين', 'Right'], ['BILATERAL', 'ثنائي الجانب', 'Bilateral']],
        ];
    }
}
