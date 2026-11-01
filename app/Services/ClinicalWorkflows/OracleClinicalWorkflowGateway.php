<?php

namespace App\Services\ClinicalWorkflows;

use App\Exceptions\ClinicalWorkflowException;
use App\Models\WcDailyReview;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use BackedEnum;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Stringable;

/**
 * Calls the Oracle PL/SQL procedures of wccms_oracle_procedures.sql (Oracle 11g or later).
 *
 * The form data is sent as XML, not JSON: 11g has no JSON support and the hospital schema already owns a type named
 * JSON_OBJECT_T. The procedures read the XML with XMLTYPE.
 *
 * Each procedure takes its IN parameters plus an OUT p_result holding the code of the row it created, and does not
 * COMMIT: the call runs inside DB::transaction, so a raised error (RAISE_APPLICATION_ERROR -20001) rolls everything back.
 */
class OracleClinicalWorkflowGateway implements ClinicalWorkflowGatewayInterface
{
    /**
     * The XML payload is bound as a PL/SQL string, which Oracle caps at 32767 bytes.
     */
    private const MAX_PAYLOAD_BYTES = 32767;

    /**
     * Element name of each value of a list.
     */
    private const LIST_ITEM = 'item';

    /**
     * @param  array<string, mixed>  $payload
     */
    public function acceptRegistration(WcRequestReg $request, array $payload): WcRegistration
    {
        $code = $this->call('SP_WC_ACCEPT_REGISTRATION', [
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
        $code = $this->call('SP_WC_ADD_WOUND', [
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
        $code = $this->call('SP_WC_COMPLETE_REVIEW', [
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
     * Encode the payload as `<payload>...</payload>` for the procedures.
     *
     * Associative keys become child elements of the same name, lists become repeated `<item>` elements, null values are
     * left out, booleans become 1/0 and floats use '.' as the decimal separator. No XML declaration: the CLOB is
     * converted to the database character set on the way in.
     *
     * @param  array<array-key, mixed>  $payload
     *
     * @throws ClinicalWorkflowException
     */
    private function encode(array $payload): string
    {
        $xml = '<payload>'.$this->encodeChildren($payload).'</payload>';

        if (strlen($xml) > self::MAX_PAYLOAD_BYTES) {
            throw new ClinicalWorkflowException('بيانات التقييم أطول من المسموح. اختصر الملاحظات وحاول مجدداً.');
        }

        return $xml;
    }

    /**
     * An array with only integer keys is a list (even after filtering left gaps); any string key makes it a map.
     *
     * @param  array<array-key, mixed>  $values
     *
     * @throws ClinicalWorkflowException
     */
    private function encodeChildren(array $values): string
    {
        $isList = array_filter(array_keys($values), is_string(...)) === [];
        $xml = '';

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            $name = $isList ? self::LIST_ITEM : $this->elementName($key);
            $xml .= "<{$name}>".$this->encodeValue($value)."</{$name}>";
        }

        return $xml;
    }

    /**
     * @throws ClinicalWorkflowException
     */
    private function encodeValue(mixed $value): string
    {
        return match (true) {
            is_array($value) => $this->encodeChildren($value),
            is_bool($value) => $value ? '1' : '0',
            is_int($value) => (string) $value,
            is_float($value) => $this->encodeFloat($value),
            is_string($value) => $this->encodeText($value),
            $value instanceof BackedEnum => $this->encodeText((string) $value->value),
            $value instanceof Stringable => $this->encodeText((string) $value),
            default => throw new ClinicalWorkflowException('تعذر تجهيز بيانات التقييم السريري.'),
        };
    }

    /**
     * Plain decimal notation with '.' whatever the locale (sprintf %F ignores it), never exponent notation.
     *
     * @throws ClinicalWorkflowException
     */
    private function encodeFloat(float $value): string
    {
        if (! is_finite($value)) {
            throw new ClinicalWorkflowException('تعذر تجهيز بيانات التقييم السريري.');
        }

        $formatted = rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');

        return $formatted === '-0' ? '0' : $formatted;
    }

    /**
     * Escape text for an XML element, after repairing invalid UTF-8 and dropping the characters XML 1.0 does not
     * allow (control characters other than tab, line feed and carriage return, and U+FFFE/U+FFFF).
     */
    private function encodeText(string $value): string
    {
        $value = (string) preg_replace(
            '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u',
            '',
            mb_scrub($value, 'UTF-8')
        );

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * The payload key as an XML element name. Keys are fixed field names, so anything else is a programming error.
     *
     * @throws ClinicalWorkflowException
     */
    private function elementName(int|string $key): string
    {
        $name = (string) $key;

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_.-]*$/', $name) !== 1 || str_starts_with(strtolower($name), 'xml')) {
            throw new ClinicalWorkflowException('تعذر تجهيز بيانات التقييم السريري.');
        }

        return $name;
    }
}
