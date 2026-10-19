<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthlyReportRequest;
use App\Models\WcDepartment;
use App\Models\WcReportExport;
use App\Services\Reports\MonthlyCaseReportService;
use App\Services\Reports\MonthlyReportWorkbook;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MonthlyReportController extends Controller
{
    private const XLSX_CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function __construct(public MonthlyCaseReportService $reports) {}

    public function cases(MonthlyReportRequest $request): View
    {
        $month = $request->month();
        $departmentCodes = $request->departmentCodes();

        return view('reports.monthly.cases', [
            'month' => $month,
            'cases' => $this->reports->cases($month, $month, $departmentCodes),
            'departments' => $this->departments(),
            'selectedDepartments' => $departmentCodes,
        ]);
    }

    public function summary(MonthlyReportRequest $request): View
    {
        [$from, $to] = $request->monthRange();
        $departmentCodes = $request->departmentCodes();

        return view('reports.monthly.summary', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->reports->summary($from, $to, $departmentCodes),
            'departments' => $this->departments(),
            'selectedDepartments' => $departmentCodes,
        ]);
    }

    public function readmissions(MonthlyReportRequest $request): View
    {
        [$from, $to] = $request->monthRange(1);
        $departmentCodes = $request->departmentCodes();

        return view('reports.monthly.readmissions', [
            'from' => $from,
            'to' => $to,
            'readmissions' => $this->reports->readmissions($from, $to, $departmentCodes),
            'departments' => $this->departments(),
            'selectedDepartments' => $departmentCodes,
        ]);
    }

    /**
     * The `.xlsx` export of the case list, summary and readmissions for a month range; every export is recorded.
     */
    public function export(MonthlyReportRequest $request, MonthlyReportWorkbook $workbook): BinaryFileResponse
    {
        [$from, $to] = $request->monthRange(1);
        $departmentCodes = $request->departmentCodes();

        $cases = $this->reports->cases($from, $to, $departmentCodes);
        $path = tempnam(sys_get_temp_dir(), 'wccms-report-');

        $workbook->write(
            $path,
            $cases,
            $this->reports->summarize($cases, $from, $to),
            $this->reports->readmissionsAmong($cases),
        );

        WcReportExport::create([
            'WC_RPEX_USER_CD' => $request->user()->PER_USER_CODE,
            'WC_RPEX_EXPORTED_AT' => now(),
            'WC_RPEX_PERIOD_FROM' => $from->toDateString(),
            'WC_RPEX_PERIOD_TO' => $to->toDateString(),
            'WC_RPEX_DEPARTMENT_CODES' => $departmentCodes === [] ? null : $departmentCodes,
            'WC_RPEX_CASE_COUNT' => $cases->count(),
        ]);

        $fileName = $from->equalTo($to)
            ? 'monthly-cases-'.$from->format('Y-m').'.xlsx'
            : 'monthly-cases-'.$from->format('Y-m').'-to-'.$to->format('Y-m').'.xlsx';

        return response()
            ->download($path, $fileName, ['Content-Type' => self::XLSX_CONTENT_TYPE])
            ->deleteFileAfterSend();
    }

    /**
     * @return Collection<int, WcDepartment>
     */
    private function departments(): Collection
    {
        return WcDepartment::query()->orderBy('WC_DEPT_NAME')->get(['WC_DEPT_CODE', 'WC_DEPT_NAME']);
    }
}
