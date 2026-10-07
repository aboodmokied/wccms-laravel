<?php

namespace App\Http\Controllers;

use App\Models\MrPatient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientMedicalRecordController extends Controller
{
    /**
     * Display the patient search page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $search = trim((string) $request->query('search', ''));
        $patients = collect();

        if ($search !== '') {
            $patients = MrPatient::query()
                ->where('MR_PAT_ID_NO', $search)
                ->orWhere('MR_PAT_CODE', $search)
                ->orWhere('MR_PAT_FULL_NAME', 'LIKE', "%{$search}%")
                ->limit(20)
                ->get();

            // If only one patient found, redirect straight to their profile
            if ($patients->count() === 1) {
                return redirect()->route('patients.show', $patients->first()->MR_PAT_CODE);
            }
        }

        return view('patients.search', compact('search', 'patients'));
    }

    /**
     * Display a specific patient's profile and list of registrations.
     */
    public function show(string $patientCode): View
    {
        $patient = MrPatient::with([
            'registrations' => function ($q) {
                $q->orderBy('WC_REG_DATE', 'desc');
            },
            'registrations.department',
            'registrations.nurse',
            'registrations.dischargingNurse',
            'registrations.diagnoses.woundType',
            'registrations.diagnoses.diagnosis',
            'registrations.diagnoses.clinicalEmployee',
            'registrations.dailyReviews' => function ($q) {
                $q->orderBy('WC_DREV_VISIT_DATE', 'desc');
            },
            'registrations.dailyReviews.nurse',
            'registrations.dailyReviews.entryUser',
            'registrations.dailyReviews.scheduledReview',
            'registrations.dailyReviews.details.regDiagnosis.woundType',
            'registrations.dailyReviews.details.regDiagnosis.diagnosis',
            'registrations.dailyReviews.details.dressing',
            'registrations.dailyReviews.details.medication',
            'registrations.scheduledReviews' => function ($q) {
                $q->orderBy('WC_SREV_SCHEDULED_DATE', 'desc');
            },
            'registrations.scheduledReviews.createdBy',
        ])->findOrFail($patientCode);

        return view('patients.show', compact('patient'));
    }
}
