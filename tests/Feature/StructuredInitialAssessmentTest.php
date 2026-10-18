<?php

namespace Tests\Feature;

use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Tests\StructuredWoundTestCase;

class StructuredInitialAssessmentTest extends StructuredWoundTestCase
{
    public function test_three_wounds_create_isolated_complete_baselines_and_shared_context(): void
    {
        $payload = $this->registrationPayload();
        $payload['diagnoses'] = [$this->initialAssessment(), $this->initialAssessment(), $this->initialAssessment()];

        $registration = app(ClinicalWorkflowGatewayInterface::class)->acceptRegistration($this->pendingRequest(), $payload);

        $this->assertSame(3, $registration->diagnoses()->count());
        $this->assertSame(3, $registration->diagnoses()->withCount('initialAssessment')->get()->sum('initial_assessment_count'));
        $this->assertSame('متابعة الالتئام', $registration->WC_REG_FOLLOW_UP_OBJECTIVE);
        $this->assertDatabaseCount('wc_wound_assessment_tb', 3);
    }
}
