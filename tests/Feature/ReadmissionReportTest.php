<?php

namespace Tests\Feature;

use App\Services\Reports\CaseOutcome;
use App\Services\Reports\ReadmissionRow;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WorkbookSyntheticCases;

class ReadmissionReportTest extends TestCase
{
    use RefreshDatabase;
    use WorkbookSyntheticCases;

    public function test_lists_the_later_registration_with_the_previous_date_and_outcome(): void
    {
        $patient = $this->reportPatient();
        $this->registerCase([
            'patient' => $patient,
            'registeredAt' => CarbonImmutable::parse('2025-11-22 09:00'),
            'status' => 'DISCHARGED',
            'dischargedAt' => CarbonImmutable::parse('2025-11-28 12:00'),
        ]);
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-12-01 09:00')]);

        $response = $this->get(route('reports.monthly.readmissions', ['from' => '2025-12', 'to' => '2025-12']));

        $readmissions = $response->viewData('readmissions');
        $this->assertCount(1, $readmissions);
        $this->assertSame('2025-12-01', $readmissions->first()->case->registeredAt->toDateString());
        $this->assertSame('2025-11-22', $readmissions->first()->previousRegisteredAt->toDateString());
        $this->assertSame(CaseOutcome::Discharged, $readmissions->first()->previousOutcome);
        $response->assertSeeInOrder(['2025-11-22', 'Discharged']);
    }

    public function test_first_registration_is_never_a_readmission(): void
    {
        $patient = $this->reportPatient();
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-11-22 09:00')]);
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-12-01 09:00')]);

        $response = $this->get(route('reports.monthly.readmissions', ['from' => '2025-11', 'to' => '2025-11']));

        $this->assertCount(0, $response->viewData('readmissions'));
        $response->assertSee('لا توجد حالات إعادة دخول في هذه الفترة.');
    }

    public function test_counts_a_previous_registration_from_any_earlier_time(): void
    {
        $patient = $this->reportPatient();
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2024-10-05 09:00')]);
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-11-03 09:00')]);

        $response = $this->get(route('reports.monthly.readmissions', ['from' => '2025-11', 'to' => '2025-11']));

        $this->assertSame(
            ['2024-10-05'],
            $response->viewData('readmissions')->map(fn (ReadmissionRow $row): string => $row->previousRegisteredAt->toDateString())->all(),
        );
    }

    public function test_reproduces_the_six_workbook_readmissions(): void
    {
        $this->seedWorkbookCases();

        $response = $this->get(route('reports.monthly.readmissions', ['from' => '2025-11', 'to' => '2025-12']));

        $readmissions = $response->viewData('readmissions');
        $this->assertCount(6, $readmissions);
        $this->assertSame(
            ['2025-11' => 1, '2025-12' => 5],
            $readmissions->countBy(fn (ReadmissionRow $row): string => $row->case->registeredAt->format('Y-m'))->all(),
        );
    }

    public function test_defaults_to_the_current_month(): void
    {
        $this->travelTo(CarbonImmutable::parse('2025-12-15 10:00'));
        $patient = $this->reportPatient();
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-11-01 09:00')]);
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-11-20 09:00')]);
        $this->registerCase(['patient' => $patient, 'registeredAt' => CarbonImmutable::parse('2025-12-02 09:00')]);

        $response = $this->get(route('reports.monthly.readmissions'));

        $readmissions = $response->viewData('readmissions');
        $this->assertCount(1, $readmissions);
        $this->assertSame('2025-11-20', $readmissions->first()->previousRegisteredAt->toDateString());
    }
}
