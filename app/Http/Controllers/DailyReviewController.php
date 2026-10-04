<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyReviewRequest;
use App\Models\PerEmployee;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyReviewController extends Controller
{
    /**
     * Render the main Daily Reviews index page for all patients needing a review today.
     */
    public function dailyIndex(Request $request)
    {
        $todayDate = now()->toDateString();
        $search = $request->input('search');

        $baseQuery = WcRegistration::where('WC_REG_STATUS', 'ACTIVE')
            ->with([
                'patient',
                'department',
                'nurse',
                'diagnoses.woundType',
                'diagnoses.diagnosis',
                'dailyReviews' => function ($query) {
                    $query->orderBy('WC_DREV_VISIT_DATE', 'desc');
                },
            ]);

        if ($search) {
            $baseQuery->whereHas('patient', function ($q) use ($search) {
                $q->where('MR_PAT_FULL_NAME', 'like', "%{$search}%")
                    ->orWhere('MR_PAT_ID_NO', 'like', "%{$search}%");
            });
        }

        // Active registrations needing daily review TODAY (no review logged today)
        $pendingTodayRegistrations = (clone $baseQuery)
            ->whereDoesntHave('dailyReviews', function ($query) use ($todayDate) {
                $query->whereDate('WC_DREV_VISIT_DATE', $todayDate);
            })
            ->orderBy('WC_REG_DATE', 'desc')
            ->get();

        // Active registrations already reviewed TODAY
        $completedTodayRegistrations = (clone $baseQuery)
            ->whereHas('dailyReviews', function ($query) use ($todayDate) {
                $query->whereDate('WC_DREV_VISIT_DATE', $todayDate);
            })
            ->orderBy('WC_REG_DATE', 'desc')
            ->get();

        return view('reviews.daily_index', [
            'pendingTodayRegistrations' => $pendingTodayRegistrations,
            'completedTodayRegistrations' => $completedTodayRegistrations,
            'search' => $search,
        ]);
    }

    /**
     * Render the daily review workspace view for a specific registration.
     */
    public function index(string $registrationId)
    {
        $registration = WcRegistration::with([
            'patient',
            'department',
            'procedure',
            'nurse',
            'dischargingNurse',
            'diagnoses.woundType',
            'diagnoses.diagnosis',
            'diagnoses.clinicalEmployee',
            'diagnoses.entryUser',
            'diagnoses.dailyReviewDetails.dressing',
            'diagnoses.dailyReviewDetails.medication',
            'diagnoses.dailyReviewDetails.dailyReview',
            'dailyReviews' => function ($query) {
                $query->orderBy('WC_DREV_VISIT_DATE', 'desc');
            },
            'dailyReviews.nurse',
            'dailyReviews.entryUser',
            'dailyReviews.scheduledReview',
            'dailyReviews.details.regDiagnosis.diagnosis',
            'dailyReviews.details.regDiagnosis.woundType',
            'dailyReviews.details.dressing',
            'dailyReviews.details.medication',
        ])->findOrFail($registrationId);

        $employees = PerEmployee::orderBy('PER_EMP_FULL_NAME')->get();
        $dressings = WcDressing::where('WC_DRESS_IS_ACTIVE', 1)->orderBy('WC_DRESS_NAME')->get();
        $medications = WcMedication::where('WC_MED_IS_ACTIVE', 1)->orderBy('WC_MED_NAME')->get();

        $todayDate = now()->toDateString();

        $scheduledReviewCode = request('scheduled_review') ?? request('scheduled_review_code');
        $scheduledReview = $scheduledReviewCode
            ? WcScheduledReview::where('WC_SREV_CODE', $scheduledReviewCode)->where('WC_SREV_STATUS', 'PENDING')->first()
            : null;

        $isReviewedToday = $registration->dailyReviews
            ->filter(function ($review) use ($todayDate) {
                return Carbon::parse($review->WC_DREV_VISIT_DATE)->toDateString() === $todayDate;
            })
            ->isNotEmpty();

        return view('reviews.daily', [
            'registration' => $registration,
            'employees' => $employees,
            'dressings' => $dressings,
            'medications' => $medications,
            'isReviewedToday' => $isReviewedToday,
            'scheduledReview' => $scheduledReview,
        ]);
    }

    /**
     * Store today's multi-wound daily review visit.
     */
    public function store(StoreDailyReviewRequest $request, string $registrationId)
    {
        $registration = WcRegistration::with('diagnoses')->findOrFail($registrationId);

        if ($registration->WC_REG_STATUS === 'DISCHARGED') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'لا يمكن إضافة تقييم يومي جديد لمريض مخرج'], 403);
            }

            return redirect()->back()->with('error', 'لا يمكن إضافة تقييم يومي جديد لمريض مخرج');
        }

        if ($registration->diagnoses->count() === 0) {
            return redirect()->back()->with('error', 'يجب إضافة تشخيص جرح واحد على الأقل للمريض قبل البدء في الزيارات اليومية');
        }

        DB::transaction(function () use ($request, $registration) {
            $nurseCode = $request->validated('nurse_code');
            $overallStatus = $request->validated('overall_status');
            $generalNotes = $request->validated('general_notes');
            $entryUserCode = Auth::user()->PER_USER_CODE;

            // 1. Create visit header record
            $dailyReview = WcDailyReview::create([
                'WC_DREV_CODE' => (string) Str::uuid(),
                'WC_DREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
                'WC_DREV_SCHEDULED_REVIEW_CD' => null,
                'WC_DREV_VISIT_DATE' => now(),
                'WC_DREV_NURSE_CD' => $nurseCode,
                'WC_DREV_ENTRY_USER_CD' => $entryUserCode,
                'WC_DREV_OVERALL_STATUS' => $overallStatus,
                'WC_DREV_GENERAL_NOTES' => $generalNotes,
            ]);

            // 2. Create detail records for each active wound
            foreach ($request->validated('details') as $detail) {
                WcDailyReviewDetail::create([
                    'WC_REVD_CODE' => (string) Str::uuid(),
                    'WC_REVD_REVIEW_CD' => $dailyReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $detail['diagnosis_code'],
                    'WC_DREVD_DRESSING_CD' => $detail['dressing_code'] ?? null,
                    'WC_DREVD_MEDICATION_CD' => $detail['medication_code'] ?? null,
                    'WC_DREVD_WOUND_DESCRIPTION' => $detail['observation'] ?? null,
                    'WC_DREVD_STATUS' => $detail['outcome_status'],
                ]);
            }

            // 3. Discharge registration if overall status is IMPROVED_DISCHARGE or IMPROVED_SCHEDULED
            if (in_array($overallStatus, ['IMPROVED_DISCHARGE', 'IMPROVED_SCHEDULED'])) {
                $registration->update([
                    'WC_REG_STATUS' => 'DISCHARGED',
                    'WC_REG_DISCHARGED_AT' => now(),
                    'WC_REG_DISCHARGED_BY_EMP_CD' => $nurseCode,
                ]);
            }

            // 4. Create pending scheduled review appointment if overall status is IMPROVED_SCHEDULED
            if ($overallStatus === 'IMPROVED_SCHEDULED') {
                $scheduledDate = $request->validated('scheduled_date')
                    ? Carbon::parse($request->validated('scheduled_date'))
                    : now()->addDays(7);

                $visitSerial = WcScheduledReview::where('WC_SREV_REGISTRATION_CD', $registration->WC_REG_CODE)->count() + 1;

                $scheduledReview = WcScheduledReview::create([
                    'WC_SREV_CODE' => (string) Str::uuid(),
                    'WC_SREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
                    'WC_SREV_SCHEDULED_DATE' => $scheduledDate,
                    'WC_SREV_VISIT_SERIAL' => $visitSerial,
                    'WC_SREV_STATUS' => 'PENDING',
                ]);

                $dailyReview->update([
                    'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduledReview->WC_SREV_CODE,
                ]);
            }

            // 5. Fulfill existing pending scheduled review if fulfillment was triggered
            $scheduledReviewCode = $request->input('scheduled_review_code') ?? $request->input('scheduled_review');
            if ($scheduledReviewCode) {
                $pendingScheduledReview = WcScheduledReview::where('WC_SREV_CODE', $scheduledReviewCode)
                    ->where('WC_SREV_STATUS', 'PENDING')
                    ->first();

                if ($pendingScheduledReview) {
                    $pendingScheduledReview->update([
                        'WC_SREV_STATUS' => 'COMPLETED',
                        'WC_SREV_COMPLETED_ON' => now(),
                    ]);
                    $dailyReview->update([
                        'WC_DREV_SCHEDULED_REVIEW_CD' => $pendingScheduledReview->WC_SREV_CODE,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'تم تسجيل التقييم والزيارة اليومية بنجاح');
    }
}
