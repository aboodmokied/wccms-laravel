<?php

namespace Tests\Feature;

use App\Models\WcAssessmentChoice;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentChoiceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_label_and_order_without_changing_stable_identity(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $choice = WcAssessmentChoice::query()->firstOrFail();
        $identity = [$choice->WC_ACH_CATEGORY, $choice->WC_ACH_KEY];

        $response = $this->put(route('admin.assessment_choices.update', $choice), [
            'label_en' => 'Approved Clinical Term',
            'label_ar' => 'تسمية سريرية معتمدة',
            'display_order' => 44,
            'WC_ACH_CATEGORY' => 'TAMPERED',
            'WC_ACH_KEY' => 'TAMPERED',
        ]);

        $response->assertRedirect();
        $choice->refresh();
        $this->assertSame('Approved Clinical Term', $choice->WC_ACH_LABEL_EN);
        $this->assertSame('Approved Clinical Term', $choice->displayLabel());
        $this->assertSame('تسمية سريرية معتمدة', $choice->WC_ACH_LABEL_AR);
        $this->assertSame(44, $choice->WC_ACH_DISPLAY_ORDER);
        $this->assertSame($identity, [$choice->WC_ACH_CATEGORY, $choice->WC_ACH_KEY]);
    }

    public function test_rejects_a_choice_saved_without_an_english_name(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $choice = WcAssessmentChoice::query()->firstOrFail();
        $englishName = $choice->WC_ACH_LABEL_EN;

        $response = $this->put(route('admin.assessment_choices.update', $choice), [
            'label_en' => '',
            'label_ar' => 'تسمية',
            'display_order' => 1,
        ]);

        $response->assertSessionHasErrors(['label_en' => 'حقل الاسم الإنجليزي مطلوب.']);
        $this->assertSame($englishName, $choice->fresh()->WC_ACH_LABEL_EN);
    }

    public function test_a_nurse_can_retire_a_choice_and_its_label_remains_readable(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $choice = WcAssessmentChoice::query()->firstOrFail();
        $label = $choice->WC_ACH_LABEL_AR;

        $this->patch(route('admin.assessment_choices.toggle', $choice))
            ->assertRedirect();

        $choice->refresh();
        $this->assertFalse($choice->WC_ACH_IS_ACTIVE);
        $this->assertSame($label, $choice->WC_ACH_LABEL_AR);
    }
}
