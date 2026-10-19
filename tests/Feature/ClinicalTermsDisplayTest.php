<?php

namespace Tests\Feature;

use App\Models\WcAssessmentChoice;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Tests\StructuredWoundTestCase;

/**
 * Spec 014: medical terms are displayed in English on every page; administrative text stays Arabic.
 */
class ClinicalTermsDisplayTest extends StructuredWoundTestCase
{
    public function test_registration_form_shows_medical_terms_in_english_and_administrative_labels_in_arabic(): void
    {
        $request = $this->pendingRequest();

        $response = $this->call('GET', route('requests.show', $request->WC_REQ_CODE), [], $this->cookies());

        foreach (['Type of Wound', 'Diagnosis', 'Wound Status', 'Exudate Amount', 'Exudate Type', 'Wound Bed', 'Wound Margins',
            'Pain Severity', 'Type of DSG', 'Medication', 'Procedure', 'Allergies?', 'PMH', 'Swab Taken?',
            'Granulation', 'Necrotic', 'Purulent', 'Pocketing', 'First Visit'] as $term) {
            $response->assertSee($term);
        }
        foreach (['القسم', 'الممرض السريري', 'هدف المتابعة'] as $administrative) {
            $response->assertSee($administrative);
        }
        foreach (['نسيج حبيبي', 'كمية الإفرازات', 'سرير الجرح', 'نوع الإفرازات', 'الأمراض المصاحبة'] as $translated) {
            $response->assertDontSee($translated);
        }
    }

    public function test_daily_review_form_lists_overall_and_wound_outcomes_in_english(): void
    {
        [$registration] = $this->registrationWithWound();

        $response = $this->call('GET', route('reviews.daily.create', $registration), [], $this->cookies());

        $response->assertSeeInOrder(['Overall Outcome', 'Improved – Continuing Treatment', 'Worsened – Admitted', 'Death']);
        $response->assertSeeInOrder(['Wound Outcome', 'Improved – Follow-up', 'Healed', 'Worsened', 'Deceased']);
        $response->assertDontSee('تدهور ودخول المستشفى');
    }

    public function test_missing_medical_field_message_is_arabic_and_names_the_english_field(): void
    {
        $request = $this->pendingRequest();
        $payload = $this->registrationPayload();
        unset($payload['diagnoses'][0]['exudate_amount_choice_code']);

        $response = $this->call('POST', route('requests.accept', $request->WC_REQ_CODE), $payload, $this->cookies());

        $response->assertSessionHasErrors(['diagnoses.0.exudate_amount_choice_code' => 'حقل Exudate Amount مطلوب.']);
        $this->assertDatabaseCount('wc_registration_tb', 0);
    }

    public function test_review_timeline_shows_outcomes_and_values_in_english_and_free_text_as_written(): void
    {
        [$registration, $wound] = $this->registrationWithWound();
        app(ClinicalWorkflowGatewayInterface::class)->completeReview($registration, [
            'submission_key' => (string) Str::uuid(), 'nurse_code' => $this->employee->PER_EMP_CODE,
            'entry_user_code' => $this->user->PER_USER_CODE, 'overall_status' => 'IMPROVED_STAY',
            'details' => [$this->followupAssessment($wound)],
        ]);

        $response = $this->call('GET', route('registrations.show', $registration), [], $this->cookies());

        $response->assertSee('Improved – Continuing Treatment');
        $response->assertSee('Improved – Follow-up');
        $response->assertSee('Improving');
        $response->assertSee('Granulation');
        $response->assertSee('نسيج حبيبي جيد');
        $response->assertDontSee('تحسن مع استمرار العلاج');
        $response->assertDontSee('تحسن ومتابعة');
    }

    public function test_every_seeded_choice_has_an_english_name_and_others_fall_back_to_their_key(): void
    {
        $unnamed = WcAssessmentChoice::create([
            'WC_ACH_CATEGORY' => 'WOUND_BED', 'WC_ACH_KEY' => 'HYPER_GRANULATION', 'WC_ACH_LABEL_AR' => 'فرط تحبب',
            'WC_ACH_IS_ACTIVE' => 1, 'WC_ACH_DISPLAY_ORDER' => 99,
        ]);

        $this->assertSame(0, WcAssessmentChoice::query()->whereKeyNot($unnamed->getKey())->whereNull('WC_ACH_LABEL_EN')->count());
        $this->assertSame('Hyper Granulation', $unnamed->displayLabel());
    }

    /**
     * @return array<string, string>
     */
    private function cookies(): array
    {
        return ['hospital_token' => JWT::encode([
            'sub' => $this->user->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], config('services.hospital_auth.jwt_secret'), 'HS256')];
    }
}
