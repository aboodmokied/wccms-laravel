<?php

namespace Tests\Feature;

use App\Exceptions\ClinicalWorkflowException;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use Tests\StructuredWoundTestCase;

class StructuredWoundAdditionTest extends StructuredWoundTestCase
{
    public function test_addition_creates_baseline_and_discharged_registration_rejects_it(): void
    {
        [$registration] = $this->registrationWithWound();
        $payload = ['assessment' => $this->initialAssessment(), 'clinic_nurse_code' => $this->employee->PER_EMP_CODE, 'entry_user_code' => $this->user->PER_USER_CODE];
        $created = app(ClinicalWorkflowGatewayInterface::class)->addWound($registration, $payload);
        $this->assertNotNull($created->initialAssessment()->first());

        $registration->update(['WC_REG_STATUS' => 'DISCHARGED']);
        $this->expectException(ClinicalWorkflowException::class);
        app(ClinicalWorkflowGatewayInterface::class)->addWound($registration->fresh(), $payload);
    }
}
