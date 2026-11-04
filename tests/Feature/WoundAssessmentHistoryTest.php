<?php

namespace Tests\Feature;

use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Tests\StructuredWoundTestCase;

class WoundAssessmentHistoryTest extends StructuredWoundTestCase
{
    public function test_longitudinal_record_renders_structured_baseline_under_owning_wound(): void
    {
        app(ClinicalWorkflowGatewayInterface::class)->acceptRegistration($this->pendingRequest(), $this->registrationPayload());

        $response = $this->get(route('patients.show', $this->patient));

        $response->assertOk()
            ->assertSee('التقييم الأولي')
            ->assertSee('First Visit')
            ->assertDontSee('الزيارة الأولى')
            ->assertSee('20.25')
            ->assertSee('mm')
            ->assertSee('ممرض منظم');
    }
}
