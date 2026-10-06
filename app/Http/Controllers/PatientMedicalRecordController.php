<?php

namespace App\Http\Controllers;

use App\Models\MrPatient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientMedicalRecordController extends Controller
{
    /**
     * Display patient medical record search and history.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $selectedPatientCode = $request->query('patient_code');

        $patients = collect();
        $selectedPatient = null;

        if ($search !== '') {
            $query = MrPatient::query()
                ->where('MR_PAT_ID_NO', $search)
                ->orWhere('MR_PAT_CODE', $search)
                ->orWhere('MR_PAT_FULL_NAME', 'LIKE', "%{$search}%");

            $patients = $query->limit(10)->get();

            if ($selectedPatientCode) {
                $selectedPatient = MrPatient::with([
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
                ])->find($selectedPatientCode);
            } elseif ($patients->count() === 1) {
                $selectedPatient = MrPatient::with([
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
                ])->find($patients->first()->MR_PAT_CODE);
            }
        }

        return view('patients.medical_record', compact('search', 'patients', 'selectedPatient'));
    }
}
