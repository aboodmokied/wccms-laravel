<?php

namespace App\Services\Reports;

use Carbon\CarbonInterface;

/**
 * Display formatting shared by the monthly report pages and the Excel export.
 */
final class ReportFormat
{
    public const LIST_SEPARATOR = '، ';

    /**
     * Case-list column headers in workbook order (FR-004); the doctor specialty column is deferred.
     */
    public const CASE_COLUMNS = [
        '#',
        'اسم المريض',
        'الشهر',
        'رقم الهوية',
        'الجنس',
        'سنة الميلاد',
        'التشخيص',
        'مكان الجرح ووصفه',
        'تاريخ الدخول للمستشفى',
        'التاريخ المرضي',
        'نوع الجرح',
        'الضماد',
        'الدواء',
        'الإجراء',
        'القسم',
        'الصور',
        'تاريخ الخروج',
        'النتيجة',
    ];

    public const PREVIOUS_COLUMNS = ['التسجيل السابق', 'نتيجة التسجيل السابق'];

    /**
     * Arabic month and year, e.g. "نوفمبر 2025".
     */
    public static function month(CarbonInterface $month): string
    {
        return $month->copy()->locale('ar')->translatedFormat('F Y');
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
