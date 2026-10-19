<?php

namespace App\Services\Reports;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Writes the monthly report export: case list, summary and readmissions, each on a right-to-left sheet.
 */
class MonthlyReportWorkbook
{
    public const CASES_SHEET = 'الحالات';

    public const SUMMARY_SHEET = 'الملخص';

    public const READMISSIONS_SHEET = 'إعادة الدخول';

    private const DATE_FORMAT = 'yyyy-mm-dd';

    private Style $headerStyle;

    private Style $dateStyle;

    public function __construct()
    {
        $this->headerStyle = (new Style)->withFontBold(true);
        $this->dateStyle = (new Style)->withFormat(self::DATE_FORMAT);
    }

    /**
     * @param  Collection<int, CaseRow>  $cases
     * @param  array{
     *     perMonth: list<array{month: CarbonInterface, count: int}>,
     *     perGender: list<array{label: string, count: int}>,
     *     perDiagnosis: list<array{label: string, count: int}>,
     *     totalCases: int
     * }  $summary
     * @param  Collection<int, ReadmissionRow>  $readmissions
     */
    public function write(string $path, Collection $cases, array $summary, Collection $readmissions): void
    {
        $writer = new Writer;
        $writer->openToFile($path);

        $this->startSheet($writer, self::CASES_SHEET, false);
        $writer->addRow($this->headerRow(ReportFormat::CASE_COLUMNS));
        foreach ($cases->values() as $index => $case) {
            $writer->addRow(new Row($this->caseCells($case, $index + 1)));
        }

        $this->startSheet($writer, self::SUMMARY_SHEET, true);
        $this->writeCounts($writer, 'عدد الحالات حسب الشهر', array_map(fn (array $row): array => [
            'label' => ReportFormat::month($row['month']),
            'count' => $row['count'],
        ], $summary['perMonth']), $summary['totalCases']);
        $this->writeCounts($writer, 'الحالات حسب الجنس', $summary['perGender'], $summary['totalCases']);
        $this->writeCounts($writer, 'الحالات حسب التشخيص', $summary['perDiagnosis'], null);

        $this->startSheet($writer, self::READMISSIONS_SHEET, true);
        $writer->addRow($this->headerRow([...ReportFormat::CASE_COLUMNS, ...ReportFormat::PREVIOUS_COLUMNS]));
        foreach ($readmissions->values() as $index => $readmission) {
            $writer->addRow(new Row([
                ...$this->caseCells($readmission->case, $index + 1),
                $this->dateCell($readmission->previousRegisteredAt->startOfDay()),
                Cell::fromValue($readmission->previousOutcome->label()),
            ]));
        }

        $writer->close();
    }

    private function startSheet(Writer $writer, string $name, bool $isNewSheet): void
    {
        $sheet = $isNewSheet ? $writer->addNewSheetAndMakeItCurrent() : $writer->getCurrentSheet();
        $sheet->setName($name);
        $sheet->setSheetView(new SheetView(rightToLeft: true, freezeRow: 0));
    }

    /**
     * @param  list<string>  $headers
     */
    private function headerRow(array $headers): Row
    {
        return Row::fromValuesWithStyle($headers, $this->headerStyle);
    }

    /**
     * @return list<Cell>
     */
    private function caseCells(CaseRow $case, int $number): array
    {
        return [
            Cell::fromValue($number),
            Cell::fromValue($case->patientName),
            Cell::fromValue(ReportFormat::month($case->month())),
            Cell::fromValue($case->idNumber),
            Cell::fromValue($case->gender),
            Cell::fromValue($case->birthYear),
            Cell::fromValue(ReportFormat::list($case->diagnoses)),
            Cell::fromValue(ReportFormat::list($case->woundLocations)),
            $this->dateCell($case->hospitalAdmissionDate),
            Cell::fromValue(ReportFormat::list($case->pastMedicalHistory)),
            Cell::fromValue(ReportFormat::list($case->woundTypes)),
            Cell::fromValue(ReportFormat::list($case->dressings)),
            Cell::fromValue(ReportFormat::list($case->medications)),
            Cell::fromValue(ReportFormat::list($case->procedures)),
            Cell::fromValue($case->department),
            Cell::fromValue($case->photoCount),
            $this->dateCell($case->dischargeDate),
            Cell::fromValue($case->outcome->label()),
        ];
    }

    private function dateCell(?CarbonInterface $date): Cell
    {
        return $date === null ? Cell::fromValue(null) : Cell::fromValue($date->toDateTimeImmutable(), $this->dateStyle);
    }

    /**
     * A titled two-column count table followed by its total and an empty separator row.
     *
     * @param  list<array{label: string, count: int}>  $items
     */
    private function writeCounts(Writer $writer, string $title, array $items, ?int $total): void
    {
        $writer->addRow($this->headerRow([$title, 'العدد']));
        foreach ($items as $item) {
            $writer->addRow(Row::fromValues([$item['label'], $item['count']]));
        }
        if ($total !== null) {
            $writer->addRow(Row::fromValuesWithStyle(['المجموع', $total], $this->headerStyle));
        }
        $writer->addRow(Row::fromValues([]));
    }
}
