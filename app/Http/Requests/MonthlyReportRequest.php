<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of the monthly case reports: one month, or a month range, and optional departments.
 */
class MonthlyReportRequest extends FormRequest
{
    public const SUMMARY_DEFAULT_MONTHS = 12;

    /**
     * Every authenticated user may read the reports (spec 013, clarification Q8).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m'],
            'to' => ['nullable', 'date_format:Y-m', 'after_or_equal:from'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['string', 'exists:wc_department_tb,WC_DEPT_CODE'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'month.date_format' => 'صيغة الشهر غير صحيحة.',
            'from.date_format' => 'صيغة الشهر غير صحيحة.',
            'to.date_format' => 'صيغة الشهر غير صحيحة.',
            'to.after_or_equal' => 'شهر النهاية يجب أن يكون بعد شهر البداية أو مساوياً له.',
            'departments.array' => 'القسم المحدد غير موجود.',
            'departments.*.exists' => 'القسم المحدد غير موجود.',
        ];
    }

    /**
     * The selected month, defaulting to the current month.
     */
    public function month(): CarbonImmutable
    {
        return $this->monthValue('month') ?? CarbonImmutable::now()->startOfMonth();
    }

    /**
     * The selected month range; a missing end defaults to the current month and a missing start to
     * $defaultMonths months ending at the end month.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function monthRange(int $defaultMonths = self::SUMMARY_DEFAULT_MONTHS): array
    {
        $to = $this->monthValue('to') ?? CarbonImmutable::now()->startOfMonth();
        $from = $this->monthValue('from') ?? $to->subMonths($defaultMonths - 1);

        return [$from, $to];
    }

    /**
     * @return list<string>
     */
    public function departmentCodes(): array
    {
        return array_values(array_unique($this->validated('departments') ?? []));
    }

    /**
     * Invalid filters return to the same report without its query string, so a bad link cannot loop.
     */
    protected function getRedirectUrl(): string
    {
        return route($this->route()?->getName() ?? 'reports.monthly.cases');
    }

    private function monthValue(string $key): ?CarbonImmutable
    {
        $value = $this->validated($key);

        return $value ? CarbonImmutable::createFromFormat('!Y-m', $value)->startOfMonth() : null;
    }
}
