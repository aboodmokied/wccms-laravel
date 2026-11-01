<?php

namespace Tests\Feature;

use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Illuminate\Support\Str;
use Tests\StructuredWoundTestCase;

class StructuredDailyReviewTest extends StructuredWoundTestCase
{
    public function test_exact_coverage_creates_canonical_assessments_and_replay_is_idempotent(): void
    {
        [$registration, $first] = $this->registrationWithWound();
        $second = $first->replicate();
        $second->WC_RDIAG_CODE = (string) Str::uuid();
        $second->save();
        $payload = [
            'submission_key' => (string) Str::uuid(), 'nurse_code' => $this->employee->EMP_SERIAL,
            'entry_user_code' => $this->user->PER_USER_CODE, 'overall_status' => 'IMPROVED_STAY',
            'details' => [$this->followupAssessment($first), $this->followupAssessment($second)],
        ];
        $gateway = app(ClinicalWorkflowGatewayInterface::class);
        $review = $gateway->completeReview($registration, $payload);
        $replayed = $gateway->completeReview($registration, $payload);

        $this->assertSame($review->WC_DREV_CODE, $replayed->WC_DREV_CODE);
        $this->assertSame(2, $review->details()->count());
        $this->assertDatabaseCount('wc_wound_assessment_tb', 2);
        $this->assertDatabaseCount('wc_daily_review_tb', 1);
    }
}
