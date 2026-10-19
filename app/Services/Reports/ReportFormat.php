<?php

namespace App\Services\Reports;

use Carbon\CarbonInterface;

/**
 * Display formatting shared by the monthly report pages and the Excel export.
 */
final class ReportFormat
{
    /**
     * Values are medical terms in English (spec 014), so they are joined as in the workbook ("swap culture, debridement").
     */
    public const LIST_SEPARATOR = ', ';

    /**
     * Case-list column headers exactly as the clinic workbook (spec 014); the doctor specialty column is deferred.
     */
    public const CASE_COLUMNS = [
        'NO',
        'Pt Name',
        'MONTHS',
        'ID',
        'Gender',
        'DoB',
        'Diagnosis',
        'Discussion',
        'DoA',
        'PMH',
        'Type of Wound',
        'Type of DSG',
        'Medication',
        'Procedure',
        'Department',
        'Picture',
        'DISCHARGE',
        'Comment',
    ];

    public const PREVIOUS_COLUMNS = ['Previous Registration', 'Previous Comment'];

    /**
     * Month as written in the workbook, e.g. "Nov -2025".
     */
    public static function month(CarbonInterface $month): string
    {
        return $month->format('M -Y');
    }

    public static function date(?CarbonInterface $date): string
    {
        return $date?->format('Y-m-d') ?? '';
    }

    /**
     * @param  list<string>  $values
     */
    public static function list(array $values): string
    {
        return implode(self::LIST_SEPARATOR, $values);
    }

    /**
     * Value of a month input (`YYYY-MM`).
     */
    public static function monthInput(CarbonInterface $month): string
    {
        return $month->format('Y-m');
    }
}
