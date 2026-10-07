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
use App\Models\WcWoundPhoto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DailyReviewController extends Controller
{
    /**
     * Render the pending Daily Reviews index page for patients needing a review today.
     */
    public function pendingIndex(Request $request)
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
            ]);

        if ($search) {
            $baseQuery->whereHas('patient', function ($q) use ($search) {
                $q->where('MR_PAT_FULL_NAME', 'like', "%{$search}%")
                    ->orWhere('MR_PAT_ID_NO', 'like', "%{$search}%");
            });
        }

        $pendingRegistrations = $baseQuery
            ->whereDoesntHave('dailyReviews', function ($query) use ($todayDate) {
                $query->whereDate('WC_DREV_VISIT_DATE', $todayDate);
            })
            ->orderBy('WC_REG_DATE', 'desc')
            ->get();

        return view('reviews.daily_pending', [
            'registrations' => $pendingRegistrations,
            'search' => $search,
        ]);
    }

    /**
     * Render the completed Daily Reviews index page for patients reviewed today.
     */
    public function completedIndex(Request $request)
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
                'dailyReviews' => function ($query) use ($todayDate) {
                    $query->whereDate('WC_DREV_VISIT_DATE', $todayDate)->orderBy('WC_DREV_VISIT_DATE', 'desc');
                },
                'dailyReviews.nurse',
            ]);

        if ($search) {
            $baseQuery->whereHas('patient', function ($q) use ($search) {
                $q->where('MR_PAT_FULL_NAME', 'like', "%{$search}%")
                    ->orWhere('MR_PAT_ID_NO', 'like', "%{$search}%");
            });
        }

        $completedRegistrations = $baseQuery
            ->whereHas('dailyReviews', function ($query) use ($todayDate) {
                $query->whereDate('WC_DREV_VISIT_DATE', $todayDate);
            })
            ->orderBy('WC_REG_DATE', 'desc')
            ->get();

        return view('reviews.daily_completed', [
            'registrations' => $completedRegistrations,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new daily review (Full Page instead of Modal).
     */
    public function create(string $registrationId): View|RedirectResponse
    {
        $registration = WcRegistration::findOrFail($registrationId);

        if ($registration->WC_REG_STATUS !== 'ACTIVE') {
            return redirect()->route('reviews.daily.pending')
                ->with('error', 'لا يمكن إضافة مراجعة يومية لمريض مخرج');
        }

        return view('reviews.daily_create', $this->reviewFormData($registration));
    }

    /**
     * Show the form for documenting a pending scheduled review.
     */
    public function createScheduled(WcScheduledReview $scheduledReview): View|RedirectResponse
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->route('reviews.scheduled')
                ->with('error', 'لا يمكن توثيق موعد تم إنجازه أو إلغاؤه');
        }

        $registration = WcRegistration::findOrFail($scheduledReview->WC_SREV_REGISTRATION_CD);

        return view('reviews.daily_create', $this->reviewFormData($registration, $scheduledReview));
    }

    /**
     * Redirect the retired review workspace to the patient's health history.
     */
    public function historyRedirect(string $registrationId): RedirectResponse
    {
        $registration = WcRegistration::findOrFail($registrationId);

        return redirect()->route('patients.show', $registration->WC_REG_PATIENT_CD);
    }

    /**
     * Store today's multi-wound daily review visit.
     */
    public function store(StoreDailyReviewRequest $request, string $registrationId): RedirectResponse
    {
        $registration = WcRegistration::with('diagnoses')->findOrFail($registrationId);

        $scheduledReviewCode = $request->input('scheduled_review_code') ?? $request->input('scheduled_review');
        $isFulfillingScheduled = false;

        if ($scheduledReviewCode) {
            $isFulfillingScheduled = WcScheduledReview::where('WC_SREV_CODE', $scheduledReviewCode)
                ->where('WC_SREV_REGISTRATION_CD', $registration->WC_REG_CODE)
                ->where('WC_SREV_STATUS', 'PENDING')
                ->exists();
        }

        if ($registration->WC_REG_STATUS === 'DISCHARGED' && ! $isFulfillingScheduled) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'لا يمكن إضافة تقييم يومي جديد لمريض مخرج بدون موعد مجدول'], 403);
            }

            return redirect()->back()->with('error', 'لا يمكن إضافة تقييم يومي جديد لمريض مخرج بدون موعد مجدول');
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
            foreach ($request->validated('details') as $index => $detail) {
                $reviewDetail = WcDailyReviewDetail::create([
                    'WC_REVD_CODE' => (string) Str::uuid(),
                    'WC_REVD_REVIEW_CD' => $dailyReview->WC_DREV_CODE,
                    'WC_REVD_REVIEW_TYPE' => 'DAILY',
                    'WC_DREVD_REG_DIAGNOSIS_CD' => $detail['diagnosis_code'],
                    'WC_DREVD_DRESSING_CD' => $detail['dressing_code'] ?? null,
                    'WC_DREVD_MEDICATION_CD' => $detail['medication_code'] ?? null,
                    'WC_DREVD_WOUND_DESCRIPTION' => $detail['observation'] ?? null,
                    'WC_DREVD_STATUS' => $detail['outcome_status'],
                ]);

                $photos = $request->file("details.{$index}.wound_photos");
                if (! empty($photos) && is_array($photos)) {
                    foreach ($photos as $photoFile) {
                        if ($photoFile && $photoFile->isValid()) {
                            $path = $photoFile->store('wound_photos/'.date('Y/m'), 'public');
                            WcWoundPhoto::create([
                                'WC_PHOTO_CODE' => (string) Str::uuid(),
                                'WC_PHOTO_REG_DIAGNOSIS_CD' => $detail['diagnosis_code'],
                                'WC_PHOTO_DAILY_REVIEW_DETAIL_CD' => $reviewDetail->WC_REVD_CODE,
                                'WC_PHOTO_STAGE' => 'REVIEW',
                                'WC_PHOTO_PATH' => $path,
                                'WC_PHOTO_CAPTION' => 'صورة متابعة مراجعة الجرح',
                                'WC_PHOTO_TAKEN_AT' => now(),
                                'WC_PHOTO_NURSE_CD' => $nurseCode,
                                'WC_PHOTO_ENTRY_USER_CD' => $entryUserCode,
                            ]);
                        }
                    }
                }
            }

            // 3. Discharge registration if overall status is final discharge or hospital admission
            if (in_array($overallStatus, ['IMPROVED_DISCHARGE', 'IMPROVED_SCHEDULED', 'WORSENED_ADMISSION'])) {
                $registration->update([
                    'WC_REG_STATUS' => 'DISCHARGED',
                    'WC_REG_DISCHARGED_AT' => now(),
                    'WC_REG_DISCHARGED_BY_EMP_CD' => $nurseCode,
                ]);
            }

            // 4. Create pending scheduled review appointment if overall status requires booking a new review
            if (in_array($overallStatus, ['IMPROVED_SCHEDULED', 'WORSENED_SCHEDULED'])) {
                $scheduledDate = $request->validated('scheduled_date')
                    ? Carbon::parse($request->validated('scheduled_date'))
                    : now()->addDays(7);

                $visitSerial = WcScheduledReview::where('WC_SREV_REGISTRATION_CD', $registration->WC_REG_CODE)->count() + 1;

                $newScheduledReview = WcScheduledReview::create([
                    'WC_SREV_CODE' => (string) Str::uuid(),
                    'WC_SREV_REGISTRATION_CD' => $registration->WC_REG_CODE,
                    'WC_SREV_SCHEDULED_DATE' => $scheduledDate,
                    'WC_SREV_VISIT_SERIAL' => $visitSerial,
                    'WC_SREV_STATUS' => 'PENDING',
                ]);

                if (! $dailyReview->WC_DREV_SCHEDULED_REVIEW_CD) {
                    $dailyReview->update([
                        'WC_DREV_SCHEDULED_REVIEW_CD' => $newScheduledReview->WC_SREV_CODE,
                    ]);
                }
            }

            // 5. Fulfill existing pending scheduled review if fulfillment was triggered
            $scheduledReviewCode = $request->input('scheduled_review_code') ?? $request->input('scheduled_review');
            if ($scheduledReviewCode) {
                $pendingScheduledReview = WcScheduledReview::where('WC_SREV_CODE', $scheduledReviewCode)
                    ->where('WC_SREV_REGISTRATION_CD', $registration->WC_REG_CODE)
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

        $redirectRoute = $isFulfillingScheduled ? 'reviews.scheduled' : 'reviews.daily.pending';

        return redirect()->route($redirectRoute)->with('success', 'تم تسجيل التقييم والزيارة بنجاح');
    }

    /**
     * Store a review from the scheduled review entry page.
     */
    public function storeScheduled(StoreDailyReviewRequest $request, WcScheduledReview $scheduledReview): RedirectResponse
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->route('reviews.scheduled')
                ->with('error', 'لا يمكن توثيق موعد تم إنجازه أو إلغاؤه');
        }

        $request->merge(['scheduled_review_code' => $scheduledReview->WC_SREV_CODE]);

        return $this->store($request, $scheduledReview->WC_SREV_REGISTRATION_CD);
    }

    /**
     * Build the data required by both daily and scheduled review forms.
     *
     * @return array{
     *     registration: WcRegistration,
     *     employees: Collection<int, PerEmployee>,
     *     dressings: Collection<int, WcDressing>,
     *     medications: Collection<int, WcMedication>,
     *     scheduledReview: WcScheduledReview|null
     * }
     */
    private function reviewFormData(WcRegistration $registration, ?WcScheduledReview $scheduledReview = null): array
    {
        $registration->load([
            'patient',
            'department',
            'diagnoses.woundType',
            'diagnoses.diagnosis',
        ]);

        return [
            'registration' => $registration,
            'employees' => PerEmployee::orderBy('PER_EMP_FULL_NAME')->get(),
            'dressings' => WcDressing::where('WC_DRESS_IS_ACTIVE', 1)->orderBy('WC_DRESS_NAME')->get(),
            'medications' => WcMedication::where('WC_MED_IS_ACTIVE', 1)->orderBy('WC_MED_NAME')->get(),
            'scheduledReview' => $scheduledReview,
        ];
    }
}
