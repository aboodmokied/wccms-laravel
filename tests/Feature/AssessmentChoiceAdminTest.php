<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WcAssessmentChoice;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssessmentChoiceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_label_and_order_without_changing_stable_identity(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $choice = WcAssessmentChoice::query()->firstOrFail();
        $identity = [$choice->WC_ACH_CATEGORY, $choice->WC_ACH_KEY];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token('ADMIN'))
            ->put(route('admin.assessment_choices.update', $choice), [
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

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token('ADMIN'))
            ->put(route('admin.assessment_choices.update', $choice), [
                'label_en' => '',
                'label_ar' => 'تسمية',
                'display_order' => 1,
            ]);

        $response->assertSessionHasErrors(['label_en' => 'حقل الاسم الإنجليزي مطلوب.']);
        $this->assertSame($englishName, $choice->fresh()->WC_ACH_LABEL_EN);
    }

    public function test_non_admin_cannot_manage_choices_and_retired_label_remains_readable(): void
    {
        $this->seed(WcAssessmentChoiceSeeder::class);
        $choice = WcAssessmentChoice::query()->firstOrFail();
        $label = $choice->WC_ACH_LABEL_AR;

        $this->withHeader('Authorization', 'Bearer '.$this->token('NURSE'))
            ->patch(route('admin.assessment_choices.toggle', $choice))
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$this->token('ADMIN'))
            ->patch(route('admin.assessment_choices.toggle', $choice))
            ->assertRedirect();

        $choice->refresh();
        $this->assertFalse($choice->WC_ACH_IS_ACTIVE);
        $this->assertSame($label, $choice->WC_ACH_LABEL_AR);
    }

    private function token(string $role): string
    {
        $username = strtolower($role).'_choices_'.Str::random(6);
        User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => $username,
            'PER_USER_ROLE' => $role,
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        return JWT::encode([
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + 3600,
        ], config('services.hospital_auth.jwt_secret'), 'HS256');
    }
}
