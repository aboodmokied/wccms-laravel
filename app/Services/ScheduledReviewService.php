<?php

namespace App\Services;

use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ScheduledReviewService
{
    /**
     * Get paginated scheduled review queue according to filters.
     */
    public function getQueue(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WcScheduledReview::query()
            ->with([
                'registration.patient',
                'registration.department',
                'dailyReview',
                'createdBy',
            ]);

        $tab = strtoupper($filters['tab'] ?? 'TODAY');
        $todayStr = Carbon::today()->toDateString();

        switch ($tab) {
            case 'TODAY':
                $query->whereDate('WC_SREV_SCHEDULED_DATE', $todayStr)
                    ->where('WC_SREV_STATUS', 'PENDING');
                break;
            case 'UPCOMING':
                $query->whereDate('WC_SREV_SCHEDULED_DATE', '>', $todayStr)
                    ->where('WC_SREV_STATUS', 'PENDING');
                break;
            case 'OVERDUE':
                $query->whereDate('WC_SREV_SCHEDULED_DATE', '<', $todayStr)
                    ->where('WC_SREV_STATUS', 'PENDING');
                break;
            case 'COMPLETED':
                $query->where('WC_SREV_STATUS', 'COMPLETED');
                break;
            case 'CANCELLED':
                $query->where('WC_SREV_STATUS', 'CANCELLED');
                break;
            case 'ALL':
            default:
                // No status/date scoping filter applied
                break;
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->whereHas('registration.patient', fn ($q) => $q->matching($search));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('WC_SREV_SCHEDULED_DATE', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('WC_SREV_SCHEDULED_DATE', '<=', $filters['date_to']);
        }

        if (in_array($tab, ['COMPLETED', 'CANCELLED'])) {
            $query->orderBy('WC_SREV_SCHEDULED_DATE', 'desc');
        } else {
            $query->orderBy('WC_SREV_SCHEDULED_DATE', 'asc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get summary counters for dashboard tabs.
     */
    public function getSummaryCounters(): array
    {
        $todayStr = Carbon::today()->toDateString();

        $todayCount = WcScheduledReview::query()
            ->whereDate('WC_SREV_SCHEDULED_DATE', $todayStr)
            ->where('WC_SREV_STATUS', 'PENDING')
            ->count();

        $upcomingCount = WcScheduledReview::query()
            ->whereDate('WC_SREV_SCHEDULED_DATE', '>', $todayStr)
            ->where('WC_SREV_STATUS', 'PENDING')
            ->count();

        $overdueCount = WcScheduledReview::query()
            ->whereDate('WC_SREV_SCHEDULED_DATE', '<', $todayStr)
            ->where('WC_SREV_STATUS', 'PENDING')
            ->count();

        $completedCount = WcScheduledReview::query()
            ->where('WC_SREV_STATUS', 'COMPLETED')
            ->count();

        $cancelledCount = WcScheduledReview::query()
            ->where('WC_SREV_STATUS', 'CANCELLED')
            ->count();

        return [
            'today_count' => $todayCount,
            'upcoming_count' => $upcomingCount,
            'overdue_count' => $overdueCount,
            'completed_count' => $completedCount,
            'cancelled_count' => $cancelledCount,
        ];
    }

    /**
     * Reschedule a pending review appointment.
     */
    public function reschedule(WcScheduledReview $scheduledReview, string $newScheduledDate, ?string $notes = null): bool
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return false;
        }

        $updateData = [
            'WC_SREV_SCHEDULED_DATE' => $newScheduledDate,
        ];

        if (! empty($notes)) {
            $existingNotes = $scheduledReview->WC_SREV_NOTES;
            $updateData['WC_SREV_NOTES'] = $existingNotes
                ? $existingNotes."\n[تعديل موعد]: ".$notes
                : '[تعديل موعد]: '.$notes;
        }

        return $scheduledReview->update($updateData);
    }

    /**
     * Cancel a pending review appointment.
     */
    public function cancel(WcScheduledReview $scheduledReview, ?string $notes = null): bool
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return false;
        }

        $existingNotes = $scheduledReview->WC_SREV_NOTES;
        $formattedNotes = $notes
            ? ($existingNotes ? $existingNotes."\n[إلغاء]: ".$notes : '[إلغاء]: '.$notes)
            : $existingNotes;

        return $scheduledReview->update([
            'WC_SREV_STATUS' => 'CANCELLED',
            'WC_SREV_NOTES' => $formattedNotes,
        ]);
    }

    /**
     * Hard-delete PENDING scheduled reviews belonging to previous registrations of a patient.
     */
    public function deletePendingForPreviousRegistrations(string $patientCode, string $newRegistrationCode): int
    {
        $oldRegCodes = WcRegistration::query()
            ->where('WC_REG_PATIENT_CD', $patientCode)
            ->where('WC_REG_CODE', '!=', $newRegistrationCode)
            ->pluck('WC_REG_CODE');

        if ($oldRegCodes->isEmpty()) {
            return 0;
        }

        return WcScheduledReview::query()
            ->whereIn('WC_SREV_REGISTRATION_CD', $oldRegCodes)
            ->where('WC_SREV_STATUS', 'PENDING')
            ->delete();
    }
}
