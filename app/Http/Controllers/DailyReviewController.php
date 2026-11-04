<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyReviewRequest;
use App\Models\Employee;
use App\Models\WcAssessmentChoice;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use App\Services\WoundPhotoStorage;
use App\Support\SystemUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DailyReviewController extends Controller
{
    public function __construct(
        private ClinicalWorkflowGatewayInterface $clinicalWorkflows,
        private WoundPhotoStorage $woundPhotoStorage,
    ) {}

    public function pendingIndex(Request $request): View
    {
        $today = now()->toDateString();
        $search = $request->input('search');
        $registrations = WcRegistration::query()
            ->where('WC_REG_STATUS', 'ACTIVE')
            ->with(['patient.sex', 'department', 'nurse', 'diagnoses.woundType', 'diagnoses.diagnosis'])
            ->when($search, fn ($query) => $query->whereHas('patient', fn ($patientQuery) => $patientQuery->matching($search)))
            ->whereDoesntHave('dailyReviews', fn ($query) => $query->whereDate('WC_DREV_VISIT_DATE', $today))
            ->orderByDesc('WC_REG_DATE')
            ->get();

        return view('reviews.daily_pending', ['registrations' => $registrations, 'search' => $search]);
    }

    public function completedIndex(Request $request): View
    {
        $today = now()->toDateString();
        $search = $request->input('search');
        $registrations = WcRegistration::query()
            ->where('WC_REG_STATUS', 'ACTIVE')
            ->with([
                'patient.sex', 'department', 'nurse', 'diagnoses.woundType', 'diagnoses.diagnosis',
                'dailyReviews' => fn ($query) => $query->whereDate('WC_DREV_VISIT_DATE', $today)->latest('WC_DREV_VISIT_DATE'),
                'dailyReviews.nurse',
            ])
            ->when($search, fn ($query) => $query->whereHas('patient', fn ($patientQuery) => $patientQuery->matching($search)))
            ->whereHas('dailyReviews', fn ($query) => $query->whereDate('WC_DREV_VISIT_DATE', $today))
            ->orderByDesc('WC_REG_DATE')
            ->get();

        return view('reviews.daily_completed', ['registrations' => $registrations, 'search' => $search]);
    }

    public function create(string $registrationId): View|RedirectResponse
    {
        $registration = WcRegistration::query()->findOrFail($registrationId);
        if ($registration->WC_REG_STATUS !== 'ACTIVE') {
            return redirect()->route('reviews.daily.pending')->with('error', 'لا يمكن إضافة مراجعة يومية لمريض مخرج');
        }

        return view('reviews.daily_create', $this->reviewFormData($registration));
    }

    public function createScheduled(WcScheduledReview $scheduledReview): View|RedirectResponse
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->route('reviews.scheduled')->with('error', 'لا يمكن توثيق موعد تم إنجازه أو إلغاؤه');
        }

        return view('reviews.daily_create', $this->reviewFormData($scheduledReview->registration()->firstOrFail(), $scheduledReview));
    }

    public function historyRedirect(string $registrationId): RedirectResponse
    {
        $registration = WcRegistration::query()->findOrFail($registrationId);

        return redirect()->route('patients.show', $registration->WC_REG_PATIENT_CD);
    }

    public function store(StoreDailyReviewRequest $request, string $registrationId): RedirectResponse
    {
        return $this->persistReview($request, WcRegistration::query()->with('diagnoses')->findOrFail($registrationId));
    }

    public function storeScheduled(StoreDailyReviewRequest $request, WcScheduledReview $scheduledReview): RedirectResponse
    {
        if ($scheduledReview->WC_SREV_STATUS !== 'PENDING') {
            return redirect()->route('reviews.scheduled')->with('error', 'لا يمكن توثيق موعد تم إنجازه أو إلغاؤه');
        }

        $registration = $scheduledReview->registration()->with('diagnoses')->firstOrFail();

        return $this->persistReview($request, $registration, $scheduledReview);
    }

    private function persistReview(
        StoreDailyReviewRequest $request,
        WcRegistration $registration,
        ?WcScheduledReview $scheduledReview = null,
    ): RedirectResponse {
        $payload = $request->validated();
        $persistedPhotos = [];

        try {
            foreach ($payload['details'] as $index => &$detail) {
                $detail['photos'] = $this->woundPhotoStorage->persist(
                    $request->file("details.{$index}.wound_photos"),
                    'صورة متابعة مراجعة الجرح'
                );
                $persistedPhotos = [...$persistedPhotos, ...$detail['photos']];
                unset($detail['wound_photos']);
            }
            unset($detail);

            $payload['entry_user_code'] = SystemUser::code();
            $payload['assessed_at'] = now()->toDateTimeString();
            $this->clinicalWorkflows->completeReview($registration, $payload, $scheduledReview);
        } catch (\Throwable $exception) {
            $this->woundPhotoStorage->cleanup($persistedPhotos);

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $route = $scheduledReview ? 'reviews.scheduled' : 'reviews.daily.pending';

        return redirect()->route($route)->with('success', 'تم تسجيل التقييم والزيارة بنجاح');
    }

    /**
     * @return array{
     *   registration: WcRegistration,
     *   employees: Collection<int, Employee>,
     *   dressings: Collection<int, WcDressing>,
     *   medications: Collection<int, WcMedication>,
     *   procedures: Collection<int, WcProcedure>,
     *   assessmentChoices: \Illuminate\Support\Collection<string, Collection<int, WcAssessmentChoice>>,
     *   submissionKey: string,
     *   scheduledReview: WcScheduledReview|null
     * }
     */
    private function reviewFormData(WcRegistration $registration, ?WcScheduledReview $scheduledReview = null): array
    {
        $registration->load([
            'patient.sex', 'department', 'diagnoses.woundType', 'diagnoses.diagnosis',
            'diagnoses.latestAssessment.findings.choice',
            'diagnoses.latestAssessment.woundStatus',
            'diagnoses.latestAssessment.exudateAmount',
            'diagnoses.latestAssessment.exudateType',
            'diagnoses.latestAssessment.odor',
            'diagnoses.latestAssessment.painSeverity',
            'diagnoses.latestAssessment.dressing',
            'diagnoses.latestAssessment.medication',
        ]);

        return [
            'registration' => $registration,
            ...Employee::pickLists(),
            'dressings' => WcDressing::query()->where('WC_DRESS_IS_ACTIVE', 1)->orderBy('WC_DRESS_NAME')->get(),
            'medications' => WcMedication::query()->where('WC_MED_IS_ACTIVE', 1)->orderBy('WC_MED_NAME')->get(),
            'procedures' => WcProcedure::query()->where('WC_PROC_IS_ACTIVE', 1)->orderBy('WC_PROC_NAME')->get(),
            'assessmentChoices' => WcAssessmentChoice::query()->where('WC_ACH_IS_ACTIVE', 1)
                ->orderBy('WC_ACH_DISPLAY_ORDER')->get()->groupBy('WC_ACH_CATEGORY'),
            'submissionKey' => (string) Str::uuid(),
            'scheduledReview' => $scheduledReview,
        ];
    }
}
