<?php

namespace App\Services\ClinicalWorkflows;

use App\Models\WcDailyReview;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;

interface ClinicalWorkflowGatewayInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function acceptRegistration(WcRequestReg $request, array $payload): WcRegistration;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function addWound(WcRegistration $registration, array $payload): WcRegDiagnosis;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function completeReview(
        WcRegistration $registration,
        array $payload,
        ?WcScheduledReview $scheduledReview = null
    ): WcDailyReview;
}
