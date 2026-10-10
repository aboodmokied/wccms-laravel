<?php

namespace App\Http\Controllers;

use App\Models\MrPatient;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcRegistration;
use App\Models\WcScheduledReview;
use App\Models\WcWoundPhoto;
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
                ->where('MR_PAT_ID_NO', $search)
                ->orWhere('MR_PAT_CODE', $search)
                ->orWhere('MR_PAT_FULL_NAME', 'LIKE', "%{$search}%")
                ->limit(20)
                ->get();

            if ($patients->count() === 1) {
                return redirect()->route('patients.show', $patients->first()->MR_PAT_CODE);
            }
        }

        return view('patients.search', compact('search', 'patients'));
    }

    /**
     * Display the patient's complete longitudinal clinical record.
     */
    public function show(string $patientCode): View
    {
        $patient = MrPatient::query()
            ->with([
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
                            'diagnoses.woundPhotos' => function (HasMany $photos): void {
                                $photos
                                    ->where('WC_PHOTO_STAGE', 'INITIAL')
                                    ->whereNull('WC_PHOTO_DAILY_REVIEW_DETAIL_CD')
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
                            'diagnoses',
                            'dailyReviews',
                            'scheduledReviews',
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
