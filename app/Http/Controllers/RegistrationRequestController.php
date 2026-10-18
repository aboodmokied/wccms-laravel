<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptRegistrationRequest;
use App\Http\Requests\MarkIneligibleRequest;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcPmh;
use App\Models\WcProcedure;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use App\Services\ClinicalWorkflows\ClinicalWorkflowGatewayInterface;
use App\Services\HospitalPatientSyncServiceInterface;
use App\Services\WoundPhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegistrationRequestController extends Controller
{
    public function __construct(
        private ClinicalWorkflowGatewayInterface $clinicalWorkflows,
        private WoundPhotoStorage $woundPhotoStorage,
    ) {}

    public function index(Request $request)
    {
        $query = $request->input('search');

        $requests = WcRequestReg::with(['patient', 'nurse', 'entryUser', 'reviewer'])
            ->when($query, function ($q) use ($query) {
                $q->where('WC_REQ_CODE', 'LIKE', "%{$query}%")
                    ->orWhere('WC_REQ_PAT_NAME_SNAPSHOT', 'LIKE', "%{$query}%")
                    ->orWhere('WC_REQ_PAT_ID_SNAPSHOT', 'LIKE', "%{$query}%");
            })
            ->orderByRaw("CASE WHEN WC_REQ_STATUS = 'PENDING' THEN 1 WHEN WC_REQ_STATUS = 'VISIT_IN_PROGRESS' THEN 2 ELSE 3 END")
            ->orderBy('WC_REQ_CREATED_AT', 'desc')
            ->paginate(15);

        return view('requests.index', compact('requests', 'query'));
    }

    public function myRequests(Request $request)
    {
        $userCode = Auth::user()?->PER_USER_CODE;

        $requests = WcRequestReg::with(['patient', 'nurse', 'reviewer'])
            ->where('WC_REQ_ENTRY_USER_CD', $userCode)
            ->orderBy('WC_REQ_CREATED_AT', 'desc')
            ->paginate(15);

        return view('requests.my_requests', compact('requests'));
    }

    public function create()
    {
        $employees = PerEmployee::orderBy('PER_EMP_FULL_NAME')->get();

        return view('requests.create', compact('employees'));
    }

    public function searchPatient(Request $request, HospitalPatientSyncServiceInterface $syncService)
    {
        $query = $request->input('query');

        if (! $query) {
            return response()->json(['found' => false, 'message' => 'Query parameter missing'], 400);
        }

        $result = $syncService->searchAndSyncPatient($query);
        /** @var MrPatient $patient */
        $patient = $result['patient'];

        $hasPendingRequest = WcRequestReg::where('WC_REQ_PATIENT_CD', $patient->MR_PAT_CODE)
            ->whereIn('WC_REQ_STATUS', ['PENDING', 'VISIT_IN_PROGRESS'])
            ->exists();

        $hasActiveRegistration = WcRegistration::where('WC_REG_PATIENT_CD', $patient->MR_PAT_CODE)
            ->where('WC_REG_STATUS', 'ACTIVE')
            ->exists();

        return response()->json([
            'found' => true,
            'patient' => [
                'code' => $patient->MR_PAT_CODE,
                'id_number' => $patient->MR_PAT_ID_NO,
                'full_name' => $patient->MR_PAT_FULL_NAME,
                'gender' => $patient->MR_PAT_GENDER,
                'dob' => $patient->MR_PAT_DOB,
                'phone' => $patient->MR_PAT_PHONE,
            ],
            'admission' => $result['admission'],
            'has_pending_request' => $hasPendingRequest,
            'has_active_registration' => $hasActiveRegistration,
        ]);
    }

    public function store(StoreRegistrationRequest $request)
    {
        $patient = MrPatient::where('MR_PAT_CODE', $request->validated('patient_code'))->firstOrFail();
        $userCode = Auth::user()?->PER_USER_CODE;

        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_PAT_PHONE_SNAPSHOT' => $patient->MR_PAT_PHONE,
            'WC_REQ_NURSE_CD' => $request->validated('attending_nurse_code'),
            'WC_REQ_ENTRY_USER_CD' => $userCode,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_NOTES' => $request->validated('notes'),
            'WC_REQ_CREATED_AT' => now(),
        ]);

        return redirect()->route('requests.my')->with('success', 'تم تقديم طلب التحويل للعيادة بنجاح وهو قيد المراجعة');
    }

    public function show(WcRequestReg $requestModel)
    {
        $requestModel->load(['patient', 'nurse', 'entryUser', 'reviewer']);
        $employees = PerEmployee::orderBy('PER_EMP_FULL_NAME')->get();
        $departments = WcDepartment::where('WC_DEPT_IS_ACTIVE', 1)->get();
        $procedures = WcProcedure::where('WC_PROC_IS_ACTIVE', 1)->get();
        $woundTypes = WcWoundType::where('WC_WTYPE_IS_ACTIVE', 1)->get();
        $diagnoses = WcDiagnosis::where('WC_DIAG_IS_ACTIVE', 1)->get();
        $medications = WcMedication::where('WC_MED_IS_ACTIVE', 1)->get();
        $dressings = WcDressing::where('WC_DRESS_IS_ACTIVE', 1)->orderBy('WC_DRESS_NAME')->get();
        $pmhConditions = WcPmh::where('WC_PMH_IS_ACTIVE', 1)->orderBy('WC_PMH_NAME')->get();
        $assessmentChoices = WcAssessmentChoice::query()
            ->where('WC_ACH_IS_ACTIVE', 1)
            ->orderBy('WC_ACH_DISPLAY_ORDER')
            ->get()
            ->groupBy('WC_ACH_CATEGORY');

        return view('requests.show', [
            'request' => $requestModel,
            'employees' => $employees,
            'departments' => $departments,
            'procedures' => $procedures,
            'woundTypes' => $woundTypes,
            'diagnoses' => $diagnoses,
            'medications' => $medications,
            'dressings' => $dressings,
            'pmhConditions' => $pmhConditions,
            'assessmentChoices' => $assessmentChoices,
        ]);
    }

    public function startVisit(Request $request, WcRequestReg $requestModel)
    {
        if ($requestModel->WC_REQ_STATUS !== 'PENDING') {
            return redirect()->back()->with('error', 'لا يمكن بدء الزيارة لطلب ليس في حالة قيد الانتظار');
        }

        $clinicNurseCode = $request->input('clinic_nurse_code')
            ?? PerEmployee::first()?->PER_EMP_CODE;

        $requestModel->update([
            'WC_REQ_STATUS' => 'VISIT_IN_PROGRESS',
            'WC_REQ_REVIEWED_BY_CD' => $clinicNurseCode,
        ]);

        return redirect()->route('requests.show', $requestModel->WC_REQ_CODE)
            ->with('success', 'تم بدء زيارة المريض في القسم بنجاح');
    }

    public function accept(AcceptRegistrationRequest $request, WcRequestReg $requestModel)
    {
        if (! in_array($requestModel->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS'])) {
            return redirect()->back()->with('error', 'طلب التسجيل هذا قد تم البت فيه سابقاً');
        }

        $validated = $request->validated();
        $persistedPhotos = [];

        try {
            foreach ($validated['diagnoses'] as $index => &$diagnosis) {
                $diagnosis['photos'] = $this->woundPhotoStorage->persist(
                    $request->file("diagnoses.{$index}.wound_photos"),
                    'صورة أولية للجرح عند قبول التسجيل'
                );
                $persistedPhotos = [...$persistedPhotos, ...$diagnosis['photos']];
                unset($diagnosis['wound_photos']);
            }
            unset($diagnosis);

            $validated['entry_user_code'] = Auth::user()->PER_USER_CODE;
            $validated['assessed_at'] = now()->toDateTimeString();
            $registration = $this->clinicalWorkflows->acceptRegistration($requestModel, $validated);
        } catch (\Throwable $exception) {
            $this->woundPhotoStorage->cleanup($persistedPhotos);

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('registrations.show', $registration->WC_REG_CODE)
            ->with('success', 'تم قبول الطلب وحفظ التقييمات الأولية لكل الجروح بنجاح');
    }

    public function markIneligible(MarkIneligibleRequest $request, WcRequestReg $requestModel)
    {
        if (! in_array($requestModel->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS'])) {
            return redirect()->back()->with('error', 'طلب التسجيل هذا قد تم البت فيه سابقاً');
        }

        $clinicNurseCode = $request->input('clinic_nurse_code')
            ?? $requestModel->WC_REQ_REVIEWED_BY_CD
            ?? PerEmployee::first()?->PER_EMP_CODE;

        $notes = $requestModel->WC_REQ_NOTES
            ? $requestModel->WC_REQ_NOTES."\n[سبب عدم الأهلية]: ".$request->validated('reason')
            : '[سبب عدم الأهلية]: '.$request->validated('reason');

        $requestModel->update([
            'WC_REQ_STATUS' => 'NOT_ELIGIBLE',
            'WC_REQ_REVIEWED_BY_CD' => $clinicNurseCode,
            'WC_REQ_NOTES' => $notes,
        ]);

        return redirect()->route('requests.index')
            ->with('success', 'تم تسجيل عدم أهلية المريض لتسجيل العيادة');
    }

    public function cancel(WcRequestReg $requestModel)
    {
        if ($requestModel->WC_REQ_STATUS !== 'PENDING') {
            return redirect()->back()->with('error', 'يمكن إلغاء الطلبات التي في حالة قيد الانتظار فقط');
        }

        $requestModel->update([
            'WC_REQ_STATUS' => 'CANCELLED',
        ]);

        return redirect()->route('requests.my')
            ->with('success', 'تم إلغاء طلب التحويل بنجاح');
    }
}
