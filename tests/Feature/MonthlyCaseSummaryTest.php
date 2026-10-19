<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WorkbookSyntheticCases;

class MonthlyCaseSummaryTest extends TestCase
{
    use RefreshDatabase;
    use WorkbookSyntheticCases;

    public function test_reproduces_the_workbook_figures_for_november_and_december_2025(): void
    {
        $this->seedWorkbookCases();

        $response = $this->call('GET', route('reports.monthly.summary', ['from' => '2025-11', 'to' => '2025-12']), [], $this->reportCookies());

        $summary = $response->viewData('summary');
        $this->assertSame([['2025-11', 125], ['2025-12', 79]], $this->monthCounts($summary));
        $this->assertSame([['label' => 'Male', 'count' => 122], ['label' => 'Female', 'count' => 82]], $summary['perGender']);
        $this->assertSame([
            ['label' => 'DF', 'count' => 69],
            ['label' => 'Inf Wound', 'count' => 55],
            ['label' => 'bedsore PEU', 'count' => 51],
            ['label' => 'Burn', 'count' => 11],
            ['label' => 'SSI', 'count' => 11],
            ['label' => 'Row Area', 'count' => 7],
        ], $summary['perDiagnosis']);
        $this->assertSame(204, $summary['totalCases']);
    }

    public function test_case_with_two_diagnoses_counts_under_each_but_once_per_month_and_gender(): void
    {
        $this->registerCase([
            'patient' => $this->reportPatient(['MR_PAT_GENDER' => 'FEMALE']),
            'wounds' => [
                ['diagnosis' => 'DF', 'woundType' => 'Surgical'],
                ['diagnosis' => 'bedsore PEU', 'woundType' => 'Pressure'],
            ],
        ]);

        $response = $this->call('GET', route('reports.monthly.summary', ['from' => '2025-11', 'to' => '2025-11']), [], $this->reportCookies());

        $summary = $response->viewData('summary');
        $this->assertSame([['2025-11', 1]], $this->monthCounts($summary));
        $this->assertSame([['label' => 'Male', 'count' => 0], ['label' => 'Female', 'count' => 1]], $summary['perGender']);
        $this->assertSame([['label' => 'DF', 'count' => 1], ['label' => 'bedsore PEU', 'count' => 1]], $summary['perDiagnosis']);
        $this->assertSame(1, $summary['totalCases']);
    }

    public function test_defaults_to_the_last_twelve_months_including_empty_months(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-03-10 10:00'));
        $this->registerCase(['registeredAt' => CarbonImmutable::parse('2025-04-02 09:00')]);
        $this->registerCase(['registeredAt' => CarbonImmutable::parse('2025-03-31 09:00')]);

        $response = $this->call('GET', route('reports.monthly.summary'), [], $this->reportCookies());

        $months = $this->monthCounts($response->viewData('summary'));
        $this->assertCount(12, $months);
        $this->assertSame(['2025-04', 1], $months[0]);
        $this->assertSame(['2026-03', 0], $months[11]);
        $this->assertSame(1, $response->viewData('summary')['totalCases']);
    }

    public function test_lists_unspecified_gender_only_when_a_case_has_none(): void
    {
        $this->registerCase(['patient' => $this->reportPatient(['MR_PAT_GENDER' => ''])]);

        $response = $this->call('GET', route('reports.monthly.summary', ['from' => '2025-11', 'to' => '2025-11']), [], $this->reportCookies());

        $this->assertSame(
            [['label' => 'Male', 'count' => 0], ['label' => 'Female', 'count' => 0], ['label' => 'Unspecified', 'count' => 1]],
            $response->viewData('summary')['perGender'],
        );
    }

    public function test_renders_every_count_beside_its_bar(): void
    {
        $this->seedWorkbookCases();

        $response = $this->call('GET', route('reports.monthly.summary', ['from' => '2025-11', 'to' => '2025-12']), [], $this->reportCookies());

        $response->assertSeeInOrder(['Nov -2025', '125', 'Dec -2025', '79']);
        $response->assertSeeInOrder(['Male', '122', 'Female', '82']);
    }

    public function test_rejects_an_end_month_before_the_start_month(): void
    {
        $response = $this->call('GET', route('reports.monthly.summary', ['from' => '2025-12', 'to' => '2025-11']), [], $this->reportCookies());

        $response->assertRedirect(route('reports.monthly.summary'));
        $response->assertSessionHasErrors(['to' => 'شهر النهاية يجب أن يكون بعد شهر البداية أو مساوياً له.']);
    }

    /**
     * @param  array{perMonth: list<array{month: CarbonImmutable, count: int}>}  $summary
     * @return list<array{0: string, 1: int}>
     */
    private function monthCounts(array $summary): array
    {
        return array_map(fn (array $row): array => [$row['month']->format('Y-m'), $row['count']], $summary['perMonth']);
    }
}
