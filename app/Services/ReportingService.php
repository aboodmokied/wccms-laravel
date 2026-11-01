<?php

namespace App\Services;

use App\Models\MrPatient;
use App\Models\WcDailyReview;
use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use App\Support\Hospital;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class ReportingService
{
    /**
     * Calculate executive KPI metrics with optional date period filtering, for the configured hospital only.
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
        $hospitalNo = Hospital::number();

        $activePatientsQuery = DB::table('wc_registration_tb')
            ->where('WC_REG_HOS_NO', $hospitalNo)
            ->where('WC_REG_STATUS', 'REGISTERED');
        if ($startDate) {
            $activePatientsQuery->where('WC_REG_DATE', '>=', $startDate);
        }
        $activePatientsCount = $activePatientsQuery->count();

        // 2. Pending Requests Count
        $pendingRequestsCount = DB::table('wc_request_reg_tb')
            ->where('WC_REQ_HOS_NO', $hospitalNo)
            ->where('WC_REQ_STATUS', 'PENDING')
            ->count();

        // 3. Average Treatment Duration in Days
        $registrationsQuery = DB::table('wc_registration_tb')->where('WC_REG_HOS_NO', $hospitalNo);
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
        $reviewsQuery = DB::table('wc_daily_review_tb')->where('WC_DREV_HOS_NO', $hospitalNo);
        if ($startDate) {
            $reviewsQuery->where('WC_DREV_VISIT_DATE', '>=', $startDate);
        }

        $outcomeCounts = $reviewsQuery
            ->select('WC_DREV_OVERALL_STATUS', DB::raw('COUNT(*) as "total"'))
            ->groupBy('WC_DREV_OVERALL_STATUS')
            ->pluck('total', 'WC_DREV_OVERALL_STATUS')
            ->toArray();

        $totalEvaluations = array_sum($outcomeCounts);

        $outcomesMap = [
            'DISCHARGED' => ['label' => __('clinical.outcome_groups.DISCHARGED'), 'count' => 0, 'percentage' => 0.0, 'color' => 'success'],
            'FOLLOWUP' => ['label' => __('clinical.outcome_groups.FOLLOWUP'), 'count' => 0, 'percentage' => 0.0, 'color' => 'primary'],
            'WORSENED' => ['label' => __('clinical.outcome_groups.WORSENED'), 'count' => 0, 'percentage' => 0.0, 'color' => 'warning'],
            'DECEASED' => ['label' => __('clinical.outcome_groups.DECEASED'), 'count' => 0, 'percentage' => 0.0, 'color' => 'danger'],
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
        // Schedules have no HOS_NO of their own: they belong to the hospital of their registration.
        $scheduledQuery = DB::table('wc_scheduled_review_tb')
            ->join('wc_registration_tb', 'wc_registration_tb.WC_REG_CODE', '=', 'wc_scheduled_review_tb.WC_SREV_REGISTRATION_CD')
            ->where('wc_registration_tb.WC_REG_HOS_NO', $hospitalNo)
            ->whereIn('WC_SREV_STATUS', ['COMPLETED', 'MISSED', 'CANCELLED']);
        if ($startDate) {
            $scheduledQuery->where('WC_SREV_SCHEDULED_DATE', '>=', $startDate);
        }

        $scheduledStats = $scheduledQuery
            ->select('WC_SREV_STATUS', DB::raw('COUNT(*) as "total"'))
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
     * Retrieve recent clinical activity log (configured hospital only).
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $reviews = WcDailyReview::query()
            ->with(['registration.patient', 'nurse'])
            ->orderByDesc('WC_DREV_VISIT_DATE')
            ->limit($limit)
            ->get();

        $statusLabels = [
            'DISCHARGED' => __('clinical.outcome_groups.DISCHARGED'),
            'FOLLOWUP' => __('clinical.outcome_groups.FOLLOWUP'),
            'WORSENED' => __('clinical.outcome_groups.WORSENED'),
            'DECEASED' => __('clinical.outcome_groups.DECEASED'),
        ];

        return $reviews->map(function (WcDailyReview $review) use ($statusLabels) {
            $patient = $review->registration?->patient;

            return [
                'id' => $review->WC_DREV_CODE,
                'date' => $review->getRawOriginal('WC_DREV_VISIT_DATE'),
                'patient_name' => $patient?->full_name ?: 'غير محدد',
                'patient_code' => $patient?->MRP_PATREC_CODE,
                'nurse_name' => $review->nurse?->full_name ?: 'غير محدد',
                'status_label' => $statusLabels[$review->WC_DREV_OVERALL_STATUS] ?? $review->WC_DREV_OVERALL_STATUS,
                'status' => $review->WC_DREV_OVERALL_STATUS,
            ];
        })->toArray();
    }

    /**
     * Search patient clinical timeline and return unified history payload (configured hospital only).
     *
     * A number is tried as the document number (MRP_ID) or medical record number (MRP_PATREC_CODE) first, then the
     * words are matched against the patient's Arabic names.
     */
    public function searchPatientHistory(?string $search): ?array
    {
        $search = trim((string) $search);
        if ($search === '') {
            return null;
        }

        $patient = MrPatient::query()->with('sex')->byNumber($search)->first()
            ?? MrPatient::query()->with('sex')->matching($search)->orderedByName()->first();

        if (! $patient) {
            return null;
        }

        $registrations = WcRegistration::query()
            ->where('WC_REG_PATIENT_CD', $patient->MRP_PATREC_CODE)
            ->with([
                'department',
                'nurse',
                'dailyReviews' => fn ($query) => $query->orderByDesc('WC_DREV_VISIT_DATE'),
                'dailyReviews.nurse',
                'scheduledReviews' => fn ($query) => $query->orderByDesc('WC_SREV_SCHEDULED_DATE'),
                'scheduledReviews.createdBy.employee',
            ])
            ->orderByDesc('WC_REG_DATE')
            ->get();

        return [
            'patient' => [
                'code' => $patient->MRP_PATREC_CODE,
                'name' => $patient->full_name,
                'civil_id' => $patient->MRP_ID ?? 'غير مسجل',
                // C_SEX_TB's Arabic name; never guessed from the code (config/hospital.php "sex").
                'gender' => $patient->sex_label ?: 'غير معروف',
                'phone' => $patient->MRP_MOBILE_NO ?? 'غير مسجل',
                'birth_date' => $patient->MRP_DOB?->toDateString(),
            ],
            'registrations' => $registrations->map(function (WcRegistration $reg) {
                return [
                    'code' => $reg->WC_REG_CODE,
                    'registration_date' => $reg->getRawOriginal('WC_REG_DATE'),
                    'discharge_date' => $reg->getRawOriginal('WC_REG_DISCHARGE_DATE'),
                    'status' => $reg->WC_REG_STATUS,
                    'status_label' => $reg->WC_REG_STATUS === 'REGISTERED' ? 'مسجل حالياً' : 'تم الخروج',
                    'department' => $reg->department?->WC_DEPT_NAME ?? 'عام',
                    'nurse' => $reg->nurse?->full_name ?: 'غير محدد',
                    'daily_reviews' => $reg->dailyReviews->map(function (WcDailyReview $rev) {
                        return [
                            'code' => $rev->WC_DREV_CODE,
                            'date' => $rev->getRawOriginal('WC_DREV_VISIT_DATE'),
                            'status_eval' => $rev->WC_DREV_OVERALL_STATUS,
                            'status_label' => Lang::has("clinical.overall_outcomes.{$rev->WC_DREV_OVERALL_STATUS}")
                                ? __("clinical.overall_outcomes.{$rev->WC_DREV_OVERALL_STATUS}")
                                : (string) $rev->WC_DREV_OVERALL_STATUS,
                            'nurse' => $rev->nurse?->full_name ?: 'غير محدد',
                            'notes' => $rev->WC_DREV_GENERAL_NOTES ?? 'لا توجد ملاحظات',
                        ];
                    })->values()->toArray(),
                    'scheduled_reviews' => $reg->scheduledReviews->map(function (WcScheduledReview $srev) {
                        return [
                            'code' => $srev->WC_SREV_CODE,
                            'scheduled_date' => $srev->getRawOriginal('WC_SREV_SCHEDULED_DATE'),
                            'status' => $srev->WC_SREV_STATUS,
                            'nurse' => $srev->createdBy?->employee?->full_name ?: 'غير محدد',
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];
    }
}
