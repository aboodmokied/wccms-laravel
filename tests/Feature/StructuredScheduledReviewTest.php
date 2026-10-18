<?php

namespace Tests\Feature;

use App\Exceptions\ClinicalWorkflowException;
use App\Models\WcScheduledReview;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Illuminate\Support\Str;
use Tests\StructuredWoundTestCase;

class StructuredScheduledReviewTest extends StructuredWoundTestCase
{
    public function test_pending_older_registration_appointment_completes_once(): void
    {
        [$registration, $wound] = $this->registrationWithWound('DISCHARGED');
        $scheduled = WcScheduledReview::create([
            'WC_SREV_REGISTRATION_CD' => $registration->WC_REG_CODE, 'WC_SREV_CREATED_BY_CD' => $this->user->PER_USER_CODE,
            'WC_SREV_SCHEDULED_DATE' => now(), 'WC_SREV_VISIT_SERIAL' => 1, 'WC_SREV_STATUS' => 'PENDING', 'WC_SREV_CREATED_ON' => now(),
        ]);
        $payload = ['submission_key' => (string) Str::uuid(), 'nurse_code' => $this->employee->PER_EMP_CODE, 'entry_user_code' => $this->user->PER_USER_CODE, 'overall_status' => 'IMPROVED_STAY', 'details' => [$this->followupAssessment($wound)]];
        app(ClinicalWorkflowGatewayInterface::class)->completeReview($registration, $payload, $scheduled);
        $this->assertSame('COMPLETED', $scheduled->fresh()->WC_SREV_STATUS);

        $this->expectException(ClinicalWorkflowException::class);
        $payload['submission_key'] = (string) Str::uuid();
        app(ClinicalWorkflowGatewayInterface::class)->completeReview($registration, $payload, $scheduled->fresh());
    }
}
