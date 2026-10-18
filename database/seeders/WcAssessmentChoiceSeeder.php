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
            foreach ($choices as $order => [$key, $label]) {
                WcAssessmentChoice::query()->firstOrCreate(
                    ['WC_ACH_CATEGORY' => $category, 'WC_ACH_KEY' => $key],
                    [
                        'WC_ACH_CODE' => Uuid::uuid5(Uuid::NAMESPACE_URL, "wccms:assessment-choice:{$category}:{$key}")->toString(),
                        'WC_ACH_LABEL_AR' => $label,
                        'WC_ACH_IS_ACTIVE' => 1,
                        'WC_ACH_DISPLAY_ORDER' => $order + 1,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, list<array{string, string}>>
     */
    private function choices(): array
    {
        return [
            'WOUND_STATUS' => [['FIRST_VISIT', 'الزيارة الأولى'], ['DETERIORATING', 'متدهور'], ['STATIC', 'ثابت'], ['IMPROVING', 'يتحسن']],
            'WOUND_BED' => [['GRANULATION', 'نسيج حبيبي'], ['NECROTIC', 'نسيج نخري'], ['SLOUGHING', 'نسيج رخو متموت'], ['EPITHELIALIZING', 'تكوّن ظهارة']],
            'WOUND_MARGIN' => [['NONE_OBSERVED', 'لا توجد علامة مسجلة'], ['ERYTHEMA', 'احمرار'], ['EDEMA', 'وذمة'], ['DELAYED_HEALING', 'تأخر الالتئام'], ['POCKETING', 'جيوب']],
            'EXUDATE_AMOUNT' => [['DRY', 'جاف'], ['LOW', 'قليل'], ['MEDIUM', 'متوسط'], ['HIGH', 'غزير']],
            'EXUDATE_TYPE' => [['WATERY', 'مائي'], ['CLOUDY', 'عكر'], ['PURULENT', 'صديدي'], ['CLEAR', 'صافي']],
            'ODOR' => [['NONE', 'لا توجد رائحة'], ['SLIGHT', 'خفيفة'], ['MODERATE', 'متوسطة'], ['OFFENSIVE', 'كريهة']],
            'PAIN_SEVERITY' => [['NONE', 'لا يوجد ألم'], ['MODERATE', 'متوسط'], ['SEVERE', 'شديد']],
            'FEVER' => [['NONE', 'لا توجد حرارة'], ['LOW', 'حرارة بسيطة'], ['HIGH', 'حرارة مرتفعة']],
            'NUTRITION' => [['WELL_NOURISHED', 'جيد التغذية'], ['MALNOURISHED', 'سوء تغذية']],
            'LOCATION' => [['HEAD', 'الرأس'], ['ABDOMEN', 'البطن'], ['BACK', 'الظهر'], ['LEG', 'الساق'], ['BUTTOCKS', 'الأرداف'], ['FOOT', 'القدم'], ['HAND', 'اليد'], ['FOREARM', 'الساعد'], ['OTHER', 'أخرى']],
            'LATERALITY' => [['NOT_APPLICABLE', 'لا ينطبق'], ['LEFT', 'يسار'], ['RIGHT', 'يمين'], ['BILATERAL', 'ثنائي الجانب']],
        ];
    }
}
