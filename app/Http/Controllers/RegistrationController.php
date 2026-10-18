<?php

namespace App\Http\Controllers;

use App\Http\Requests\DischargeRegistrationRequest;
use App\Http\Requests\StoreWoundDiagnosisRequest;
use App\Http\Requests\UpdateRegistrationRequest;
use App\Models\PerEmployee;
use App\Models\WcAssessmentChoice;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcWoundType;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use App\Services\WoundPhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function __construct(
        private ClinicalWorkflowGatewayInterface $clinicalWorkflows,
        private WoundPhotoStorage $woundPhotoStorage,
    ) {}

    /**
     * Display a listing of registered patients with status filter and search.
     */
    public function index(Request $request)
    {
        $status = strtoupper($request->input('status', 'ACTIVE'));

        if (! in_array($status, ['ACTIVE', 'DISCHARGED'])) {
            $status = 'ACTIVE';
        }

        $query = WcRegistration::with([
            'patient',
            'department',
            'procedure',
            'nurse',
            'dischargingNurse',
            'dailyReviews' => function ($q) {
                $q->orderBy('WC_DREV_VISIT_DATE', 'desc');
            },
        ])->where('WC_REG_STATUS', $status);

        if ($search = trim($request->input('search'))) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('MR_PAT_FULL_NAME', 'like', "%{$search}%")
                    ->orWhere('MR_PAT_ID_NO', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('WC_REG_DATE', 'desc')->paginate(15)->withQueryString();

        return view('registrations.index', [
            'registrations' => $registrations,
            'currentStatus' => $status,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Display single registration details page.
     */
    public function show(string $id)
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
            'diagnoses.assessments' => fn ($query) => $query->orderBy('WC_WA_ASSESSED_AT'),
            'diagnoses.assessments.findings.choice',
            'diagnoses.assessments.woundStatus',
            'diagnoses.assessments.exudateAmount',
            'diagnoses.assessments.exudateType',
            'diagnoses.assessments.odor',
            'diagnoses.assessments.painSeverity',
            'diagnoses.assessments.fever',
            'diagnoses.assessments.location',
            'diagnoses.assessments.laterality',
            'diagnoses.assessments.nutrition',
            'diagnoses.assessments.dressing',
            'diagnoses.assessments.medication',
            'diagnoses.assessments.procedure',
            'diagnoses.assessments.clinicalEmployee',
            'diagnoses.assessments.woundCareTeamEmployee',
            'diagnoses.assessments.doctorEmployee',
            'diagnoses.assessments.entryUser',
            'diagnoses.assessments.woundPhotos',
            'diagnoses.assessments.reviewDetail',
            'dailyReviews' => function ($query) {
                $query->orderBy('WC_DREV_VISIT_DATE', 'desc');
            },
            'dailyReviews.nurse',
            'dailyReviews.entryUser',
            'dailyReviews.scheduledReview',
            'dailyReviews.details.regDiagnosis.woundType',
            'dailyReviews.details.regDiagnosis.diagnosis',
            'dailyReviews.details.regDiagnosis.woundPhotos' => function ($query) {
                $query->where('WC_PHOTO_STAGE', 'INITIAL')
                    ->orderBy('WC_PHOTO_TAKEN_AT');
            },
            'dailyReviews.details.dressing',
            'dailyReviews.details.medication',
            'dailyReviews.details.woundPhotos' => function ($query) {
                $query->orderBy('WC_PHOTO_TAKEN_AT');
            },
            'dailyReviews.details.assessment.findings.choice',
            'dailyReviews.details.assessment.woundStatus',
            'dailyReviews.details.assessment.exudateAmount',
            'dailyReviews.details.assessment.exudateType',
            'dailyReviews.details.assessment.odor',
            'dailyReviews.details.assessment.painSeverity',
            'dailyReviews.details.assessment.dressing',
            'dailyReviews.details.assessment.medication',
            'dailyReviews.details.assessment.procedure',
            'dailyReviews.details.assessment.clinicalEmployee',
            'dailyReviews.details.assessment.entryUser',
            'dailyReviews.details.assessment.woundPhotos',
            'dailyReviews.details.assessment.reviewDetail',
            'scheduledReviews' => function ($query) {
                $query->where('WC_SREV_STATUS', 'PENDING')
                    ->where('WC_SREV_SCHEDULED_DATE', '>=', now()->startOfDay())
                    ->orderBy('WC_SREV_SCHEDULED_DATE');
            },
        ])->findOrFail($id);

        $healthSummary = [
            'reviewsCount' => $registration->dailyReviews->count(),
            'lastReview' => $registration->dailyReviews->first(),
            'nextScheduledReview' => $registration->scheduledReviews->first(),
        ];

        $procedures = WcProcedure::where('WC_PROC_IS_ACTIVE', 1)->get();
        $woundTypes = WcWoundType::where('WC_WTYPE_IS_ACTIVE', 1)->get();
        $diagnoses = WcDiagnosis::where('WC_DIAG_IS_ACTIVE', 1)->get();
        $dressings = WcDressing::where('WC_DRESS_IS_ACTIVE', 1)->get();
        $medications = WcMedication::where('WC_MED_IS_ACTIVE', 1)->get();
        $employees = PerEmployee::orderBy('PER_EMP_FULL_NAME')->get();
        $assessmentChoices = WcAssessmentChoice::query()
            ->where('WC_ACH_IS_ACTIVE', 1)
            ->orderBy('WC_ACH_DISPLAY_ORDER')
            ->get()
            ->groupBy('WC_ACH_CATEGORY');

        return view('registrations.show', [
            'registration' => $registration,
            'procedures' => $procedures,
            'woundTypes' => $woundTypes,
            'diagnoses' => $diagnoses,
            'dressings' => $dressings,
            'medications' => $medications,
            'employees' => $employees,
            'healthSummary' => $healthSummary,
            'assessmentChoices' => $assessmentChoices,
        ]);
    }

    /**
     * Update procedure and PMH notes.
     */
    public function update(UpdateRegistrationRequest $request, string $id)
    {
        $registration = WcRegistration::findOrFail($id);

        if ($registration->WC_REG_STATUS === 'DISCHARGED') {
            return redirect()->back()->with('error', 'لا يمكن تعديل تسجيل متوفى أو مخرج');
        }

        $registration->update([
            'WC_REG_PROCEDURE_CD' => $request->validated('procedure_code'),
            'WC_REG_PMH_NOTES' => $request->validated('pmh_notes'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث الإجراء والتاريخ المرضي بنجاح');
    }

    /**
     * Add initial wound diagnosis.
     */
    public function storeDiagnosis(StoreWoundDiagnosisRequest $request, string $id)
    {
        $registration = WcRegistration::findOrFail($id);

        if ($registration->WC_REG_STATUS === 'DISCHARGED') {
            return redirect()->back()->with('error', 'لا يمكن إضافة تشخيص جرح جديد لمريض مخرج');
        }

        $payload = $request->validated();
        $photos = [];

        try {
            $photos = $this->woundPhotoStorage->persist(
                $request->file('assessment.wound_photos'),
                'صورة أولية للجرح المضاف'
            );
            $payload['assessment']['photos'] = $photos;
            unset($payload['assessment']['wound_photos']);
            $payload['clinic_nurse_code'] = $payload['clinical_nurse_code'];
            $payload['entry_user_code'] = Auth::user()->PER_USER_CODE;
            $payload['assessed_at'] = now()->toDateTimeString();
            $this->clinicalWorkflows->addWound($registration, $payload);
        } catch (\Throwable $exception) {
            $this->woundPhotoStorage->cleanup($photos);

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', 'تم توثيق الجرح وتقييمه الأولي بنجاح');
    }

    /**
     * Discharge patient registration.
     */
    public function discharge(DischargeRegistrationRequest $request, string $id)
    {
        $registration = WcRegistration::findOrFail($id);

        if ($registration->WC_REG_STATUS === 'DISCHARGED') {
            return redirect()->back()->with('error', 'هذا المريض مخرج بالفعل من العيادة');
        }

        $registration->update([
            'WC_REG_STATUS' => 'DISCHARGED',
            'WC_REG_DISCHARGED_AT' => now(),
            'WC_REG_DISCHARGED_BY_EMP_CD' => $request->validated('discharging_nurse_code'),
        ]);

        return redirect()->back()->with('success', 'تم تخريج المريض من العيادة بنجاح');
    }
}
