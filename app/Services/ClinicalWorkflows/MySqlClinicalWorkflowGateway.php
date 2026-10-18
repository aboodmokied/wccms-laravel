<?php

namespace App\Services\ClinicalWorkflows;

use App\Exceptions\ClinicalWorkflowException;
use App\Models\WcDailyReview;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use JsonException;

class MySqlClinicalWorkflowGateway implements ClinicalWorkflowGatewayInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function acceptRegistration(WcRequestReg $request, array $payload): WcRegistration
    {
        $row = $this->call('CALL SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS(?, ?)', [
            $request->WC_REQ_CODE,
            $this->encode($payload),
        ]);

        return WcRegistration::query()->findOrFail($row->registration_code);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function addWound(WcRegistration $registration, array $payload): WcRegDiagnosis
    {
        $row = $this->call('CALL SP_WC_ADD_WOUND_WITH_ASSESSMENT(?, ?)', [
            $registration->WC_REG_CODE,
            $this->encode($payload),
        ]);

        return WcRegDiagnosis::query()->findOrFail($row->registration_diagnosis_code);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function completeReview(
        WcRegistration $registration,
        array $payload,
        ?WcScheduledReview $scheduledReview = null
    ): WcDailyReview {
        $row = $this->call('CALL SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS(?, ?, ?)', [
            $registration->WC_REG_CODE,
            $scheduledReview?->WC_SREV_CODE,
            $this->encode($payload),
        ]);

        return WcDailyReview::query()->findOrFail($row->daily_review_code);
    }

    /**
     * @param  list<mixed>  $bindings
     */
    private function call(string $sql, array $bindings): object
    {
        try {
            $rows = DB::select($sql, $bindings);
        } catch (QueryException $exception) {
            throw new ClinicalWorkflowException(
                'تعذر حفظ البيانات السريرية. تحقق من اكتمال بيانات الجروح وحاول مجدداً.',
                previous: $exception
            );
        }

        if ($rows === []) {
            throw new ClinicalWorkflowException('لم تُرجع قاعدة البيانات نتيجة للعملية السريرية.');
        }

        return $rows[0];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new ClinicalWorkflowException('تعذر تجهيز بيانات التقييم السريري.', previous: $exception);
        }
    }
}
