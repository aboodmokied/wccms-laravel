<?php

namespace App\Http\Controllers;

use App\Models\WcScheduledReview;
use App\Services\ScheduledReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledReviewController extends Controller
{
    /**
     * Display scheduled reviews queue dashboard.
     */
    public function index(Request $request, ScheduledReviewService $service): View
    {
        $filters = $request->only(['tab', 'search', 'date_from', 'date_to']);
        $counts = $service->getSummaryCounters();
        $scheduledReviews = $service->getQueue($filters);

        return view('reviews.scheduled', compact('counts', 'scheduledReviews'));
    }

    /**
     * Redirect to daily review creation / workspace with scheduled review context.
     */
    public function startFulfillment(WcScheduledReview $scheduledReview): RedirectResponse
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->back()->with('error', 'لا يمكن بدء مراجعة لموعد غير قائم (تم إنجازه أو إلغاؤه)');
        }

        return redirect()->route('registrations.daily-reviews.index', [
            'registration' => $scheduledReview->WC_SREV_REGISTRATION_CD,
            'scheduled_review' => $scheduledReview->WC_SREV_CODE,
        ]);
    }

    /**
     * Reschedule a pending appointment to a new target date.
     */
    public function reschedule(Request $request, WcScheduledReview $scheduledReview, ScheduledReviewService $service): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->back()->with('error', 'لا يمكن إعادة جدولة موعد غير قائم');
        }

        $service->reschedule($scheduledReview, $validated['scheduled_date'], $validated['notes'] ?? null);

        return redirect()->back()->with('success', 'تم إعادة جدولة الموعد بنجاح إلى '.date('Y-m-d', strtotime($validated['scheduled_date'])));
    }

    /**
     * Cancel a pending appointment with optional cancellation notes.
     */
    public function cancel(Request $request, WcScheduledReview $scheduledReview, ScheduledReviewService $service): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->back()->with('error', 'لا يمكن إلغاء موعد غير قائم');
        }

        $service->cancel($scheduledReview, $validated['notes'] ?? null);

        return redirect()->back()->with('success', 'تم إلغاء موعد المراجعة بنجاح');
    }
}
