<?php

namespace Tests\Feature;

use App\Models\WcReportExport;
use App\Services\Reports\MonthlyReportWorkbook;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;
use Tests\WorkbookSyntheticCases;
use ZipArchive;

class MonthlyReportExportTest extends TestCase
{
    use RefreshDatabase;
    use WorkbookSyntheticCases;

    public function test_downloads_three_sheets_matching_the_report_and_records_the_export(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-01-05 10:00'));
        $this->seedWorkbookCases();
        $departments = [$this->reportDepartment('Medical Male')->WC_DEPT_CODE];

        $response = $this->call('GET', route('reports.monthly.export', [
            'from' => '2025-11', 'to' => '2025-12', 'departments' => $departments,
        ]), [], $this->reportCookies());

        $response->assertDownload('monthly-cases-2025-11-to-2025-12.xlsx');
        $sheets = $this->readSheets($response->baseResponse->getFile()->getPathname());
        $this->assertSame(
            [MonthlyReportWorkbook::CASES_SHEET, MonthlyReportWorkbook::SUMMARY_SHEET, MonthlyReportWorkbook::READMISSIONS_SHEET],
            array_keys($sheets),
        );
        $this->assertCount(205, $sheets[MonthlyReportWorkbook::CASES_SHEET]);
        $this->assertSame(['عدد الحالات حسب الشهر', 'العدد'], $sheets[MonthlyReportWorkbook::SUMMARY_SHEET][0]);
        $this->assertSame(['نوفمبر 2025', 125], $sheets[MonthlyReportWorkbook::SUMMARY_SHEET][1]);
        $this->assertSame(['ديسمبر 2025', 79], $sheets[MonthlyReportWorkbook::SUMMARY_SHEET][2]);
        $this->assertSame(['المجموع', 204], $sheets[MonthlyReportWorkbook::SUMMARY_SHEET][3]);
        $this->assertCount(7, $sheets[MonthlyReportWorkbook::READMISSIONS_SHEET]);

        $export = WcReportExport::query()->sole();
        $this->assertSame($this->reportUser()->PER_USER_CODE, $export->WC_RPEX_USER_CD);
        $this->assertSame('2026-01-05 10:00:00', $export->WC_RPEX_EXPORTED_AT->toDateTimeString());
        $this->assertSame('2025-11-01', $export->WC_RPEX_PERIOD_FROM->toDateString());
        $this->assertSame('2025-12-01', $export->WC_RPEX_PERIOD_TO->toDateString());
        $this->assertSame($departments, $export->WC_RPEX_DEPARTMENT_CODES);
        $this->assertSame(204, $export->WC_RPEX_CASE_COUNT);
    }

    public function test_case_sheet_keeps_arabic_text_and_writes_dates_as_dates(): void
    {
        $patient = $this->reportPatient(['MR_PAT_FULL_NAME' => 'ثائر عبيد', 'MR_PAT_HOSPITAL_ADMISSION_DATE' => '2025-10-15']);
        $this->registerCase([
            'patient' => $patient,
            'status' => 'DISCHARGED',
            'dischargedAt' => CarbonImmutable::parse('2025-11-22 13:00'),
        ]);

        $response = $this->call('GET', route('reports.monthly.export', ['from' => '2025-11', 'to' => '2025-11']), [], $this->reportCookies());

        $response->assertDownload('monthly-cases-2025-11.xlsx');
        $row = $this->readSheets($response->baseResponse->getFile()->getPathname())[MonthlyReportWorkbook::CASES_SHEET][1];
        $this->assertSame([1, 'ثائر عبيد', 'نوفمبر 2025'], array_slice($row, 0, 3));
        $this->assertInstanceOf(DateTimeInterface::class, $row[8]);
        $this->assertSame('2025-10-15', $row[8]->format('Y-m-d'));
        $this->assertInstanceOf(DateTimeInterface::class, $row[16]);
        $this->assertSame('2025-11-22', $row[16]->format('Y-m-d'));
        $this->assertSame('خروج', $row[17]);
    }

    public function test_every_sheet_reads_right_to_left(): void
    {
        $this->registerCase();

        $response = $this->call('GET', route('reports.monthly.export', ['from' => '2025-11', 'to' => '2025-11']), [], $this->reportCookies());

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        foreach ([1, 2, 3] as $sheet) {
            $this->assertMatchesRegularExpression('/rightToLeft="(1|true)"/', $zip->getFromName("xl/worksheets/sheet{$sheet}.xml"));
        }
        $zip->close();
    }

    public function test_unauthenticated_export_redirects_and_records_nothing(): void
    {
        $response = $this->get(route('reports.monthly.export', ['from' => '2025-11', 'to' => '2025-11']));

        $response->assertRedirect(config('services.hospital_auth.login_url', '/mock-hospital-auth'));
        $this->assertDatabaseCount('wc_report_export_tb', 0);
    }

    public function test_invalid_range_exports_nothing(): void
    {
        $response = $this->call('GET', route('reports.monthly.export', ['from' => '2025-12', 'to' => '2025-11']), [], $this->reportCookies());

        $response->assertSessionHasErrors(['to' => 'شهر النهاية يجب أن يكون بعد شهر البداية أو مساوياً له.']);
        $this->assertDatabaseCount('wc_report_export_tb', 0);
    }

    /**
     * @return array<string, list<list<mixed>>>
     */
    private function readSheets(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);

        $sheets = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            $sheets[$sheet->getName()] = $rows;
        }
        $reader->close();

        return $sheets;
    }
}
