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
use PDO;
use PDOException;

/**
 * Calls the Oracle PL/SQL procedures of wccms_oracle_procedures.sql.
 *
 * Each procedure takes its IN parameters plus an OUT p_result holding the code of the row it created, and does not
 * COMMIT: the call runs inside DB::transaction, so a raised error (RAISE_APPLICATION_ERROR -20001) rolls everything back.
 */
class OracleClinicalWorkflowGateway implements ClinicalWorkflowGatewayInterface
{
    /**
     * The JSON payload is bound as a PL/SQL string, which Oracle caps at 32767 bytes.
     */
    private const MAX_PAYLOAD_BYTES = 32767;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function acceptRegistration(WcRequestReg $request, array $payload): WcRegistration
    {
        $code = $this->call('SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS', [
            'p_request' => $request->WC_REQ_CODE,
            'p_payload' => $this->encode($payload),
        ]);

        return WcRegistration::query()->findOrFail($code);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function addWound(WcRegistration $registration, array $payload): WcRegDiagnosis
    {
        $code = $this->call('SP_WC_ADD_WOUND_WITH_ASSESSMENT', [
            'p_registration' => $registration->WC_REG_CODE,
            'p_payload' => $this->encode($payload),
        ]);

        return WcRegDiagnosis::query()->findOrFail($code);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function completeReview(
        WcRegistration $registration,
        array $payload,
        ?WcScheduledReview $scheduledReview = null
    ): WcDailyReview {
        $code = $this->call('SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS', [
            'p_registration' => $registration->WC_REG_CODE,
            'p_scheduled' => $scheduledReview?->WC_SREV_CODE,
            'p_payload' => $this->encode($payload),
        ]);

        return WcDailyReview::query()->findOrFail($code);
    }

    /**
     * Run `BEGIN procedure(:in..., :p_result); END;` and return the OUT code.
     *
     * @param  array<string, string|null>  $bindings  IN parameters, in the procedure's order
     */
    private function call(string $procedure, array $bindings): string
    {
        $placeholders = implode(', ', array_map(
            fn (string $name): string => ":{$name}",
            [...array_keys($bindings), 'p_result']
        ));

        try {
            $result = DB::transaction(function () use ($procedure, $bindings, $placeholders): ?string {
                $statement = DB::getPdo()->prepare("BEGIN {$procedure}({$placeholders}); END;");

                foreach ($bindings as $name => $value) {
                    $statement->bindValue(":{$name}", $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                }

                $result = null;
                $statement->bindParam(':p_result', $result, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 36);
                $statement->execute();

                return $result;
            });
        } catch (PDOException|QueryException $exception) {
            throw new ClinicalWorkflowException(
                'تعذر حفظ البيانات السريرية. تحقق من اكتمال بيانات الجروح وحاول مجدداً.',
                previous: $exception
            );
        }

        if (! is_string($result) || trim($result) === '') {
            throw new ClinicalWorkflowException('لم تُرجع قاعدة البيانات نتيجة للعملية السريرية.');
        }

        return trim($result);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new ClinicalWorkflowException('تعذر تجهيز بيانات التقييم السريري.', previous: $exception);
        }

        if (strlen($json) > self::MAX_PAYLOAD_BYTES) {
            throw new ClinicalWorkflowException('بيانات التقييم أطول من المسموح. اختصر الملاحظات وحاول مجدداً.');
        }

        return $json;
    }
}
