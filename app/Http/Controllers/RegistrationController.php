<?php

namespace App\Http\Controllers;

use App\Http\Requests\DischargeRegistrationRequest;
use App\Http\Requests\StoreWoundDiagnosisRequest;
use App\Http\Requests\UpdateRegistrationRequest;
use App\Models\PerEmployee;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcProcedure;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcWoundType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
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
            'dailyReviews.nurse',
        ])->findOrFail($id);

        $procedures = WcProcedure::where('WC_PROC_IS_ACTIVE', 1)->get();
        $woundTypes = WcWoundType::where('WC_WTYPE_IS_ACTIVE', 1)->get();
        $diagnoses = WcDiagnosis::where('WC_DIAG_IS_ACTIVE', 1)->get();
        $dressings = WcDressing::where('WC_DRESS_IS_ACTIVE', 1)->get();
        $medications = WcMedication::where('WC_MED_IS_ACTIVE', 1)->get();
        $employees = PerEmployee::orderBy('PER_EMP_FULL_NAME')->get();

        return view('registrations.show', [
            'registration' => $registration,
            'procedures' => $procedures,
            'woundTypes' => $woundTypes,
            'diagnoses' => $diagnoses,
            'dressings' => $dressings,
            'medications' => $medications,
            'employees' => $employees,
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

        WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $request->validated('wound_type_code'),
            'WC_RDIAG_DIAGNOSIS_CD' => $request->validated('diagnosis_code'),
            'WC_RDIAG_CLINICAL_EMP_CD' => $request->validated('clinical_nurse_code'),
            'WC_RDIAG_ENTRY_USER_CD' => Auth::user()->PER_USER_CODE,
        ]);

        return redirect()->back()->with('success', 'تم توثيق تشخيص الجرح الأول بنجاح');
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
