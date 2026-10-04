<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Calculate executive KPI metrics with optional date period filtering.
     *
     * @param  string|null  $period  ('all', '30days', 'ytd')
     */
    public function getKpis(?string $period = 'all'): array
    {
        $startDate = match ($period) {
            '30days' => Carbon::now()->subDays(30)->startOfDay(),
            'ytd' => Carbon::now()->startOfYear(),
            default => null,
        };

        // 1. Active Patients Count
        $activePatientsQuery = DB::table('wc_registration_tb')
            ->where('WC_REG_STATUS', 'REGISTERED');
        if ($startDate) {
            $activePatientsQuery->where('WC_REG_DATE', '>=', $startDate);
        }
        $activePatientsCount = $activePatientsQuery->count();

        // 2. Pending Requests Count
        $pendingRequestsCount = DB::table('wc_request_reg_tb')
            ->where('WC_REQ_STATUS', 'PENDING')
            ->count();

        // 3. Average Treatment Duration in Days
        $registrationsQuery = DB::table('wc_registration_tb');
        if ($startDate) {
            $registrationsQuery->where('WC_REG_DATE', '>=', $startDate);
        }
        $registrations = $registrationsQuery->select([
            'WC_REG_DATE',
            'WC_REG_DISCHARGE_DATE',
            'WC_REG_STATUS',
        ])->get();

        $totalDays = 0;
        $countRegistrations = $registrations->count();

        foreach ($registrations as $reg) {
            $regDate = Carbon::parse($reg->WC_REG_DATE);
            $endDate = ($reg->WC_REG_STATUS === 'DISCHARGED' && $reg->WC_REG_DISCHARGE_DATE)
                ? Carbon::parse($reg->WC_REG_DISCHARGE_DATE)
                : Carbon::now();

            $totalDays += max(0, $regDate->diffInDays($endDate));
        }

        $avgTreatmentDays = $countRegistrations > 0 ? (int) round($totalDays / $countRegistrations) : 0;

        // 4. Outcome Distribution
        $reviewsQuery = DB::table('wc_daily_review_tb');
        if ($startDate) {
            $reviewsQuery->where('WC_DREV_VISIT_DATE', '>=', $startDate);
        }

        $outcomeCounts = $reviewsQuery
            ->select('WC_DREV_OVERALL_STATUS', DB::raw('COUNT(*) as total'))
            ->groupBy('WC_DREV_OVERALL_STATUS')
            ->pluck('total', 'WC_DREV_OVERALL_STATUS')
            ->toArray();

        $totalEvaluations = array_sum($outcomeCounts);

        $outcomesMap = [
            'DISCHARGED' => ['label' => 'تحسن وتم الخروج', 'count' => 0, 'percentage' => 0.0, 'color' => 'success'],
            'FOLLOWUP' => ['label' => 'تحسن يحتاج متابعة', 'count' => 0, 'percentage' => 0.0, 'color' => 'primary'],
            'WORSENED' => ['label' => 'تدهور في الحالة', 'count' => 0, 'percentage' => 0.0, 'color' => 'warning'],
            'DECEASED' => ['label' => 'متوفي', 'count' => 0, 'percentage' => 0.0, 'color' => 'danger'],
        ];

        foreach ($outcomeCounts as $status => $count) {
            if (isset($outcomesMap[$status])) {
                $outcomesMap[$status]['count'] = (int) $count;
                $outcomesMap[$status]['percentage'] = $totalEvaluations > 0
                    ? round(($count / $totalEvaluations) * 100, 1)
                    : 0.0;
            }
        }

        // 5. Missed Appointment Rate
        $scheduledQuery = DB::table('wc_scheduled_review_tb')
            ->whereIn('WC_SREV_STATUS', ['COMPLETED', 'MISSED', 'CANCELLED']);
        if ($startDate) {
            $scheduledQuery->where('WC_SREV_SCHEDULED_DATE', '>=', $startDate);
        }

        $scheduledStats = $scheduledQuery
            ->select('WC_SREV_STATUS', DB::raw('COUNT(*) as total'))
            ->groupBy('WC_SREV_STATUS')
            ->pluck('total', 'WC_SREV_STATUS')
            ->toArray();

        $totalCompletedOrMissed = ($scheduledStats['COMPLETED'] ?? 0) + ($scheduledStats['MISSED'] ?? 0) + ($scheduledStats['CANCELLED'] ?? 0);
        $missedCount = $scheduledStats['MISSED'] ?? 0;
        $missedRate = $totalCompletedOrMissed > 0 ? round(($missedCount / $totalCompletedOrMissed) * 100, 1) : 0.0;

        return [
            'period' => $period ?? 'all',
            'active_patients' => $activePatientsCount,
            'pending_requests' => $pendingRequestsCount,
            'avg_treatment_days' => $avgTreatmentDays,
            'total_evaluations' => $totalEvaluations,
            'missed_appointment_rate' => $missedRate,
            'outcome_distribution' => $outcomesMap,
        ];
    }

    /**
     * Retrieve recent clinical activity log.
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $reviews = DB::table('wc_daily_review_tb')
            ->join('wc_registration_tb', 'wc_daily_review_tb.WC_DREV_REGISTRATION_CD', '=', 'wc_registration_tb.WC_REG_CODE')
            ->join('mr_patient_tb', 'wc_registration_tb.WC_REG_PATIENT_CD', '=', 'mr_patient_tb.MR_PAT_CODE')
            ->leftJoin('per_employee_tb', 'wc_daily_review_tb.WC_DREV_NURSE_CD', '=', 'per_employee_tb.PER_EMP_CODE')
            ->select([
                'wc_daily_review_tb.WC_DREV_CODE as id',
                'wc_daily_review_tb.WC_DREV_VISIT_DATE as date',
                'wc_daily_review_tb.WC_DREV_OVERALL_STATUS as status',
                'mr_patient_tb.MR_PAT_FULL_NAME as patient_name',
                'mr_patient_tb.MR_PAT_CODE as patient_code',
                'per_employee_tb.PER_EMP_FULL_NAME as nurse_name',
            ])
            ->orderBy('wc_daily_review_tb.WC_DREV_VISIT_DATE', 'desc')
            ->limit($limit)
            ->get();

        return $reviews->map(function ($rev) {
            $statusLabels = [
                'DISCHARGED' => 'تحسن وتم الخروج',
                'FOLLOWUP' => 'تحسن ومتابعة',
                'WORSENED' => 'تدهور',
                'DECEASED' => 'وفاة',
            ];

            return [
                'id' => $rev->id,
                'date' => $rev->date,
                'patient_name' => $rev->patient_name ?? 'غير محدد',
                'patient_code' => $rev->patient_code,
                'nurse_name' => $rev->nurse_name ?? 'غير محدد',
                'status_label' => $statusLabels[$rev->status] ?? $rev->status,
                'status' => $rev->status,
            ];
        })->toArray();
    }

    /**
     * Search patient clinical timeline and return unified history payload.
     */
    public function searchPatientHistory(?string $search): ?array
    {
        if (! $search) {
            return null;
        }

        $patient = DB::table('mr_patient_tb')
            ->where('MR_PAT_CODE', $search)
            ->orWhere('MR_PAT_ID_NO', $search)
            ->orWhere('MR_PAT_NATIONAL_ID', $search)
            ->orWhere('MR_PAT_FULL_NAME', 'LIKE', "%{$search}%")
            ->first();

        if (! $patient) {
            return null;
        }

        // Registrations
        $registrations = DB::table('wc_registration_tb')
            ->leftJoin('wc_department_tb', 'wc_registration_tb.WC_REG_DEPARTMENT_CD', '=', 'wc_department_tb.WC_DEPT_CODE')
            ->leftJoin('per_employee_tb', 'wc_registration_tb.WC_REG_NURSE_CD', '=', 'per_employee_tb.PER_EMP_CODE')
            ->where('WC_REG_PATIENT_CD', $patient->MR_PAT_CODE)
            ->select([
                'wc_registration_tb.*',
                'wc_department_tb.WC_DEPT_NAME as department_name',
                'per_employee_tb.PER_EMP_FULL_NAME as nurse_name',
            ])
            ->orderBy('WC_REG_DATE', 'desc')
            ->get();

        $regIds = $registrations->pluck('WC_REG_CODE')->toArray();

        // Daily Reviews
        $dailyReviews = DB::table('wc_daily_review_tb')
            ->leftJoin('per_employee_tb', 'wc_daily_review_tb.WC_DREV_NURSE_CD', '=', 'per_employee_tb.PER_EMP_CODE')
            ->whereIn('WC_DREV_REGISTRATION_CD', $regIds)
            ->select([
                'wc_daily_review_tb.*',
                'per_employee_tb.PER_EMP_FULL_NAME as nurse_name',
            ])
            ->orderBy('WC_DREV_VISIT_DATE', 'desc')
            ->get()
            ->groupBy('WC_DREV_REGISTRATION_CD');

        // Scheduled Reviews
        $scheduledReviews = DB::table('wc_scheduled_review_tb')
            ->leftJoin('per_users_tb', 'wc_scheduled_review_tb.WC_SREV_CREATED_BY_CD', '=', 'per_users_tb.PER_USER_CODE')
            ->leftJoin('per_employee_tb', 'per_users_tb.PER_USER_CODE', '=', 'per_employee_tb.PER_EMP_USER_CD')
            ->whereIn('WC_SREV_REGISTRATION_CD', $regIds)
            ->select([
                'wc_scheduled_review_tb.*',
                'per_employee_tb.PER_EMP_FULL_NAME as nurse_name',
            ])
            ->orderBy('WC_SREV_SCHEDULED_DATE', 'desc')
            ->get()
            ->groupBy('WC_SREV_REGISTRATION_CD');

        return [
            'patient' => [
                'code' => $patient->MR_PAT_CODE,
                'name' => $patient->MR_PAT_FULL_NAME,
                'civil_id' => $patient->MR_PAT_ID_NO ?? $patient->MR_PAT_NATIONAL_ID ?? 'غير مسجل',
                'gender' => ($patient->MR_PAT_GENDER ?? 'M') === 'M' ? 'ذَكَر' : 'أُنثَى',
                'phone' => $patient->MR_PAT_PHONE ?? 'غير مسجل',
                'birth_date' => $patient->MR_PAT_DOB ?? null,
            ],
            'registrations' => $registrations->map(function ($reg) use ($dailyReviews, $scheduledReviews) {
                return [
                    'code' => $reg->WC_REG_CODE,
                    'registration_date' => $reg->WC_REG_DATE,
                    'discharge_date' => $reg->WC_REG_DISCHARGE_DATE,
                    'status' => $reg->WC_REG_STATUS,
                    'status_label' => $reg->WC_REG_STATUS === 'REGISTERED' ? 'مسجل حالياً' : 'تم الخروج',
                    'department' => $reg->department_name ?? 'عام',
                    'nurse' => $reg->nurse_name ?? 'غير محدد',
                    'daily_reviews' => ($dailyReviews->get($reg->WC_REG_CODE) ?? collect())->map(function ($rev) {
                        return [
                            'code' => $rev->WC_DREV_CODE,
                            'date' => $rev->WC_DREV_VISIT_DATE,
                            'status_eval' => $rev->WC_DREV_OVERALL_STATUS,
                            'nurse' => $rev->nurse_name ?? 'غير محدد',
                            'notes' => $rev->WC_DREV_GENERAL_NOTES ?? 'لا توجد ملاحظات',
                        ];
                    })->values()->toArray(),
                    'scheduled_reviews' => ($scheduledReviews->get($reg->WC_REG_CODE) ?? collect())->map(function ($srev) {
                        return [
                            'code' => $srev->WC_SREV_CODE,
                            'scheduled_date' => $srev->WC_SREV_SCHEDULED_DATE,
                            'status' => $srev->WC_SREV_STATUS,
                            'nurse' => $srev->nurse_name ?? 'غير محدد',
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];
    }
}
