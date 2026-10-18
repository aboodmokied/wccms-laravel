@php
    $sectionId = $sectionId ?? 'review-history';
    $sectionTitleId = $sectionId . '-title';
    $showActions = $showActions ?? true;
    $showBaselinePhotos = $showBaselinePhotos ?? true;
    $openFirstReview = $openFirstReview ?? true;
    $baselineAnchorPrefix = $baselineAnchorPrefix ?? null;
@endphp

<section class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white" id="{{ $sectionId }}" aria-labelledby="{{ $sectionTitleId }}">
    <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-clock-history text-primary fs-5" aria-hidden="true"></i>
                <h2 class="fs-5 fw-bold mb-0" id="{{ $sectionTitleId }}">سجل المراجعات</h2>
            </div>
            <p class="text-muted small mb-0">مرتبة من أحدث مراجعة إلى أقدم مراجعة، مع تفاصيل كل جرح وعلاجه.</p>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
            {{ $registration->dailyReviews->count() }} مراجعة
        </span>
    </div>

    <div class="card-body p-3 p-md-4">
        @forelse($registration->dailyReviews as $reviewIndex => $review)
            @php
                $reviewOutcome = match ($review->WC_DREV_OVERALL_STATUS) {
                    'IMPROVED_SCHEDULED' => ['تحسن مع موعد متابعة', 'info', 'bi-calendar-check'],
                    'IMPROVED_DISCHARGE' => ['تحسن وتخريج', 'success', 'bi-check-circle'],
                    'WORSENED_SCHEDULED' => ['تدهور مع موعد متابعة', 'warning', 'bi-exclamation-triangle'],
                    'WORSENED_ADMISSION' => ['تدهور وتنويم', 'danger', 'bi-hospital'],
                    'IMPROVED_STAY' => ['تحسن مع استمرار العلاج', 'success', 'bi-graph-up-arrow'],
                    'WORSENED_STAY' => ['تدهور مع استمرار العلاج', 'warning', 'bi-arrow-repeat'],
                    'DEATH' => ['وفاة', 'secondary', 'bi-x-circle'],
                    default => [$review->WC_DREV_OVERALL_STATUS ?: 'غير محدد', 'secondary', 'bi-dash-circle'],
                };
                $isScheduledReview = filled($review->WC_DREV_SCHEDULED_REVIEW_CD) && $review->scheduledReview;
            @endphp

            <details
                class="clinical-review-entry"
                id="{{ $sectionId }}-review-{{ $review->WC_DREV_CODE }}"
                data-review-code="{{ $review->WC_DREV_CODE }}"
                @if($openFirstReview && $loop->first) open @endif
            >
                <summary class="clinical-review-summary">
                    <span class="clinical-review-index">{{ $registration->dailyReviews->count() - $reviewIndex }}</span>
                    <span class="clinical-review-main">
                        <span class="d-flex flex-wrap align-items-center gap-2">
                            <strong class="text-dark" dir="ltr">
                                {{ \Illuminate\Support\Carbon::parse($review->WC_DREV_VISIT_DATE)->format('Y-m-d') }}
                            </strong>
                            <span class="text-muted small" dir="ltr">
                                {{ \Illuminate\Support\Carbon::parse($review->WC_DREV_VISIT_DATE)->format('H:i') }}
                            </span>
                            <span class="badge {{ $isScheduledReview ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }} border rounded-pill">
                                <i class="bi {{ $isScheduledReview ? 'bi-calendar-check' : 'bi-clipboard2-pulse' }} me-1" aria-hidden="true"></i>
                                {{ $isScheduledReview ? 'مراجعة مجدولة' : 'مراجعة يومية' }}
                            </span>
                        </span>
                        <span class="small text-muted mt-1">بواسطة {{ $review->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</span>
                    </span>
                    <span class="badge bg-{{ $reviewOutcome[1] }}-subtle text-{{ $reviewOutcome[1] }} border border-{{ $reviewOutcome[1] }}-subtle rounded-pill px-3 py-2 clinical-review-outcome">
                        <i class="bi {{ $reviewOutcome[2] }} me-1" aria-hidden="true"></i>{{ $reviewOutcome[0] }}
                    </span>
                    <i class="bi bi-chevron-down clinical-review-chevron" aria-hidden="true"></i>
                </summary>

                <div class="clinical-review-body">
                    <dl class="patient-audit-grid mb-3">
                        <div>
                            <dt>الموظف السريري</dt>
                            <dd>{{ $review->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</dd>
                        </div>
                        <div>
                            <dt>مدخل البيانات</dt>
                            <dd>{{ $review->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</dd>
                        </div>
                        @if($review->scheduledReview)
                            <div>
                                <dt>موعد الزيارة</dt>
                                <dd dir="ltr">{{ \Illuminate\Support\Carbon::parse($review->scheduledReview->WC_SREV_SCHEDULED_DATE)->format('Y-m-d H:i') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($review->WC_DREV_GENERAL_NOTES)
                        <div class="clinical-note mb-4">
                            <span class="d-block fw-bold small mb-1">الملاحظات العامة</span>
                            <p class="mb-0">{{ $review->WC_DREV_GENERAL_NOTES }}</p>
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h3 class="fs-6 fw-bold mb-0">تفاصيل الجروح</h3>
                        <span class="text-muted small">{{ $review->details->count() }} جرح</span>
                    </div>

                    <div class="row g-3">
                        @forelse($review->details as $detail)
                            @php
                                $detailOutcome = match ($detail->WC_DREVD_STATUS) {
                                    'IMPROVED_FOLLOWUP' => ['تحسن ومتابعة', 'success'],
                                    'IMPROVED_DISCHARGE' => ['مكتمل الشفاء', 'success'],
                                    'WORSENED' => ['تراجع', 'warning'],
                                    'DECEASED' => ['تلف أنسجة شديد', 'danger'],
                                    default => [$detail->WC_DREVD_STATUS ?: 'غير محدد', 'secondary'],
                                };
                                $baselinePhoto = $showBaselinePhotos
                                    ? $detail->regDiagnosis?->woundPhotos->first()
                                    : null;
                                $baselineAnchor = $baselineAnchorPrefix && $detail->regDiagnosis
                                    ? $baselineAnchorPrefix . '-' . $detail->regDiagnosis->WC_RDIAG_CODE
                                    : null;
                            @endphp
                            <div class="col-12 col-lg-6">
                                <article class="clinical-wound-review h-100">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                                        <div>
                                            <span class="text-muted small d-block mb-1">الجرح والتشخيص</span>
                                            <strong>{{ $detail->regDiagnosis?->woundType?->WC_WTYPE_NAME ?? 'جرح غير محدد' }}</strong>
                                            <span class="text-muted"> · {{ $detail->regDiagnosis?->diagnosis?->WC_DIAG_NAME ?? 'دون تشخيص' }}</span>
                                        </div>
                                        <span class="badge bg-{{ $detailOutcome[1] }}-subtle text-{{ $detailOutcome[1] }} border border-{{ $detailOutcome[1] }}-subtle rounded-pill">
                                            {{ $detailOutcome[0] }}
                                        </span>
                                    </div>

                                    @include('wound_assessments.partials.summary', ['assessment' => $detail->assessment])

                                    <dl class="clinical-review-details mb-0">
                                        <div>
                                            <dt>الضمادة</dt>
                                            <dd>{{ $detail->dressing?->WC_DRESS_NAME ?? 'لم تحدد' }}</dd>
                                        </div>
                                        <div>
                                            <dt>العلاج الموضعي</dt>
                                            <dd>{{ $detail->medication?->WC_MED_NAME ?? 'لم يحدد' }}</dd>
                                        </div>
                                        @if($detail->WC_DREVD_WOUND_DESCRIPTION)
                                            <div class="clinical-review-detail-wide">
                                                <dt>الملاحظة السريرية</dt>
                                                <dd>{{ $detail->WC_DREVD_WOUND_DESCRIPTION }}</dd>
                                            </div>
                                        @endif
                                        @if($detail->WC_DREVD_WOUND_CHANGES)
                                            <div class="clinical-review-detail-wide">
                                                <dt>التغير منذ الزيارة السابقة</dt>
                                                <dd>{{ $detail->WC_DREVD_WOUND_CHANGES }}</dd>
                                            </div>
                                        @endif
                                    </dl>

                                    @if($baselineAnchor && !$showBaselinePhotos)
                                        <a href="#{{ $baselineAnchor }}" class="patient-baseline-reference">
                                            <i class="bi bi-image" aria-hidden="true"></i>
                                            العودة إلى صورة خط الأساس لهذا الجرح
                                        </a>
                                    @endif

                                    @if($baselinePhoto || $detail->woundPhotos->isNotEmpty())
                                        <div class="clinical-review-photos mt-3 pt-3 border-top">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                <span class="fw-bold small"><i class="bi bi-images text-primary me-1" aria-hidden="true"></i>صور الجرح</span>
                                                <span class="text-muted small">{{ $detail->woundPhotos->count() }} صورة في هذه المراجعة</span>
                                            </div>
                                            <div class="clinical-review-photo-grid">
                                                @if($baselinePhoto)
                                                    <a href="{{ route('wound_photos.show', $baselinePhoto->WC_PHOTO_CODE) }}" target="_blank" rel="noopener" class="clinical-review-photo" data-photo-code="{{ $baselinePhoto->WC_PHOTO_CODE }}">
                                                        <img src="{{ route('wound_photos.show', $baselinePhoto->WC_PHOTO_CODE) }}" alt="صورة بداية الجرح" loading="lazy">
                                                        <span class="badge bg-success clinical-review-photo-badge">صورة البداية</span>
                                                        <small dir="ltr">{{ \Illuminate\Support\Carbon::parse($baselinePhoto->WC_PHOTO_TAKEN_AT)->format('Y-m-d') }}</small>
                                                    </a>
                                                @endif

                                                @foreach($detail->woundPhotos as $photo)
                                                    <a href="{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}" target="_blank" rel="noopener" class="clinical-review-photo" data-photo-code="{{ $photo->WC_PHOTO_CODE }}">
                                                        <img src="{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}" alt="صورة الجرح في المراجعة" loading="lazy">
                                                        <span class="badge bg-primary clinical-review-photo-badge">صورة المراجعة</span>
                                                        <small dir="ltr">{{ \Illuminate\Support\Carbon::parse($photo->WC_PHOTO_TAKEN_AT)->format('Y-m-d H:i') }}</small>
                                                        <small>التقاط سريري: <strong>{{ $photo->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong></small>
                                                        <small>إدخال الصورة: <strong>{{ $photo->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</strong></small>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="patient-inline-empty mt-3">لا توجد صور موثقة في هذه المراجعة.</p>
                                    @endif
                                </article>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted small mb-0">لا توجد تفاصيل جروح مرتبطة بهذه المراجعة.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </details>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-clipboard2-x display-5 text-muted d-block mb-3" aria-hidden="true"></i>
                <h3 class="fs-6 fw-bold">لا توجد مراجعات مسجلة</h3>
                <p class="text-muted small mb-3">ستظهر المراجعات اليومية والمجدولة هنا بعد توثيقها.</p>
                @if($showActions && $registration->WC_REG_STATUS === 'ACTIVE')
                    <a href="{{ route('reviews.daily.create', $registration->WC_REG_CODE) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>إدخال أول مراجعة
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</section>
