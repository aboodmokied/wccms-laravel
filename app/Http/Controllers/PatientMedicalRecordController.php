<?php

namespace App\Http\Controllers;

use App\Models\MrPatient;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use App\Models\WcWoundPhoto;
use App\Support\HospitalCode;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
                ->with('sex')
                ->matching($search)
                ->orderedByName()
                ->limit(20)
                ->get();

            if ($patients->count() === 1) {
                return redirect()->route('patients.show', $patients->first()->MRP_PATREC_CODE);
            }
        }

        return view('patients.search', compact('search', 'patients'));
    }

    /**
     * Display the patient's complete longitudinal clinical record.
     */
    public function show(string $patientCode): View
    {
        $patientCode = HospitalCode::parse($patientCode) ?? abort(404);

        $patient = MrPatient::query()
            ->with([
                'sex',
                'registrations' => function (HasMany $query): void {
                    $query
                        ->with([
                            'department',
                            'procedure',
                            'nurse',
                            'entryUser',
                            'dischargingNurse',
                            'diagnoses' => function (HasMany $diagnoses): void {
                                $diagnoses->orderBy('WC_RDIAG_CODE');
                            },
                            'diagnoses.woundType',
                            'diagnoses.diagnosis',
                            'diagnoses.clinicalEmployee',
                            'diagnoses.entryUser',
                            'diagnoses.assessments' => fn (HasMany $assessments) => $assessments->orderBy('WC_WA_ASSESSED_AT'),
                            'diagnoses.assessments.findings.choice',
                            'diagnoses.assessments.woundStatus',
                            'diagnoses.assessments.exudateAmount',
                            'diagnoses.assessments.exudateType',
                            'diagnoses.assessments.odor',
                            'diagnoses.assessments.painSeverity',
                            'diagnoses.assessments.dressing',
                            'diagnoses.assessments.medication',
                            'diagnoses.assessments.procedure',
                            'diagnoses.assessments.clinicalEmployee',
                            'diagnoses.assessments.woundCareTeamEmployee',
                            'diagnoses.assessments.doctorEmployee',
                            'diagnoses.assessments.entryUser',
                            'diagnoses.assessments.woundPhotos',
                            'diagnoses.assessments.reviewDetail',
                            'diagnoses.woundPhotos' => function (HasMany $photos): void {
                                $photos
                                    ->where('WC_PHOTO_STAGE', 'INITIAL')
                                    ->whereNull('WC_PHOTO_REVIEW_DETAIL_CD')
                                    ->orderBy('WC_PHOTO_TAKEN_AT')
                                    ->orderBy('WC_PHOTO_CODE');
                            },
                            'diagnoses.woundPhotos.nurse',
                            'diagnoses.woundPhotos.entryUser',
                            'dailyReviews' => function (HasMany $reviews): void {
                                $reviews
                                    ->orderByDesc('WC_DREV_VISIT_DATE')
                                    ->orderByDesc('WC_DREV_CODE');
                            },
                            'dailyReviews.nurse',
                            'dailyReviews.entryUser',
                            'dailyReviews.scheduledReview',
                            'dailyReviews.details' => function (HasMany $details): void {
                                $details->orderBy('WC_REVD_CODE');
                            },
                            'dailyReviews.details.regDiagnosis.woundType',
                            'dailyReviews.details.regDiagnosis.diagnosis',
                            'dailyReviews.details.dressing',
                            'dailyReviews.details.medication',
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
                            'dailyReviews.details.woundPhotos' => function (HasMany $photos): void {
                                $photos
                                    ->where('WC_PHOTO_STAGE', 'REVIEW')
                                    ->orderBy('WC_PHOTO_TAKEN_AT')
                                    ->orderBy('WC_PHOTO_CODE');
                            },
                            'dailyReviews.details.woundPhotos.nurse',
                            'dailyReviews.details.woundPhotos.entryUser',
                            'scheduledReviews' => function (HasMany $appointments): void {
                                $appointments
                                    ->orderByDesc('WC_SREV_SCHEDULED_DATE')
                                    ->orderByDesc('WC_SREV_CODE');
                            },
                            'scheduledReviews.createdBy',
                            'scheduledReviews.dailyReview',
                        ])
                        ->withCount([
                            'diagnoses as DIAGNOSES_COUNT',
                            'dailyReviews as DAILY_REVIEWS_COUNT',
                            'scheduledReviews as SCHEDULED_REVIEWS_COUNT',
                        ])
                        ->orderByRaw("CASE WHEN WC_REG_STATUS = 'ACTIVE' THEN 0 ELSE 1 END")
                        ->orderByDesc('WC_REG_DATE')
                        ->orderByDesc('WC_REG_CODE');
                },
            ])
            ->findOrFail($patientCode);

        $patient->registrations->each(function (WcRegistration $registration): void {
            $registration->dailyReviews->each(function (WcDailyReview $review) use ($registration): void {
                if (
                    $review->scheduledReview !== null
                    && $review->scheduledReview->WC_SREV_REGISTRATION_CD !== $registration->WC_REG_CODE
                ) {
                    $review->setRelation('scheduledReview', null);
                }

                $details = $review->details
                    ->filter(function (WcDailyReviewDetail $detail) use ($registration): bool {
                        return $detail->regDiagnosis?->WC_RDIAG_REGISTRATION_CD === $registration->WC_REG_CODE;
                    })
                    ->each(function (WcDailyReviewDetail $detail): void {
                        $photos = $detail->woundPhotos
                            ->filter(function (WcWoundPhoto $photo) use ($detail): bool {
                                return $photo->WC_PHOTO_REG_DIAGNOSIS_CD === $detail->WC_DREVD_REG_DIAGNOSIS_CD;
                            })
                            ->values();

                        $detail->setRelation('woundPhotos', $photos);
                    })
                    ->values();

                $review->setRelation('details', $details);
            });

            $registration->scheduledReviews->each(
                function (WcScheduledReview $appointment) use ($registration): void {
                    if (
                        $appointment->dailyReview !== null
                        && $appointment->dailyReview->WC_DREV_REGISTRATION_CD !== $registration->WC_REG_CODE
                    ) {
                        $appointment->setRelation('dailyReview', null);
                    }
                }
            );
        });

        $initialRegistrationCode = $patient->registrations->first()?->WC_REG_CODE;

        return view('patients.show', compact('patient', 'initialRegistrationCode'));
    }
}
