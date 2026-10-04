<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(public ReportingService $reportingService) {}

    public function history(Request $request)
    {
        $search = trim($request->query('search', ''));
        $history = $this->reportingService->searchPatientHistory($search);

        return view('reports.history', compact('history', 'search'));
    }

    public function exportCsv(string $patient)
    {
        $history = $this->reportingService->searchPatientHistory($patient);

        if (! $history) {
            return redirect()->route('reports.history')
                ->with('error', 'لم يتم العثور على سجل مريض لهذا المعرّف للتصدير.');
        }

        $patientData = $history['patient'];
        $registrations = $history['registrations'];

        $filename = "patient-{$patientData['code']}-history.csv";

        $response = new StreamedResponse(function () use ($patientData, $registrations) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Arabic support in MS Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Patient Header Summary
            fputcsv($handle, ['سجل المريض السريري الشامل - WCCMS']);
            fputcsv($handle, ['رقم المريض', $patientData['code']]);
            fputcsv($handle, ['اسم المريض', $patientData['name']]);
            fputcsv($handle, ['رقم الهوية', $patientData['civil_id']]);
            fputcsv($handle, ['الجنس', $patientData['gender']]);
            fputcsv($handle, ['رقم الهاتف', $patientData['phone']]);
            fputcsv($handle, []);

            // Registrations & Reviews
            foreach ($registrations as $regIndex => $reg) {
                fputcsv($handle, ['--- جلسة التسجيل رقم ('.($regIndex + 1).') ---']);
                fputcsv($handle, ['رمز التسجيل', $reg['code']]);
                fputcsv($handle, ['تاريخ التسجيل', $reg['registration_date']]);
                fputcsv($handle, ['تاريخ الخروج', $reg['discharge_date'] ?? 'نشط']);
                fputcsv($handle, ['الحالة', $reg['status_label']]);
                fputcsv($handle, ['القسم', $reg['department']]);
                fputcsv($handle, ['الممرض المسؤول', $reg['nurse']]);
                fputcsv($handle, []);

                // Daily Reviews Table
                fputcsv($handle, ['تقييمات المزيارات اليومية:']);
                fputcsv($handle, ['رمز التقييم', 'التاريخ', 'نتيجة التقييم', 'الممرض', 'ملاحظات']);
                if (empty($reg['daily_reviews'])) {
                    fputcsv($handle, ['لا توجد زيارات تقييم يومية سجلت لهذا التسجيل']);
                } else {
                    foreach ($reg['daily_reviews'] as $rev) {
                        fputcsv($handle, [
                            $rev['code'],
                            $rev['date'],
                            $rev['status_eval'],
                            $rev['nurse'],
                            $rev['notes'],
                        ]);
                    }
                }
                fputcsv($handle, []);

                // Scheduled Reviews Table
                fputcsv($handle, ['المواعيد المجدولة:']);
                fputcsv($handle, ['رمز الموعد', 'التاريخ المجدول', 'الحالة', 'الممرض المسؤول']);
                if (empty($reg['scheduled_reviews'])) {
                    fputcsv($handle, ['لا توجد مواعيد مجدولة']);
                } else {
                    foreach ($reg['scheduled_reviews'] as $srev) {
                        fputcsv($handle, [
                            $srev['code'],
                            $srev['scheduled_date'],
                            $srev['status'],
                            $srev['nurse'],
                        ]);
                    }
                }
                fputcsv($handle, []);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
