<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <h3 class="card-title fs-6 mb-0 fw-bold text-dark">سجل المراجعات والزيارات اليومية</h3>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fs-7 rounded-pill">
            {{ $registration->dailyReviews->count() }} {{ $registration->dailyReviews->count() === 1 ? 'زيارة مسجلة' : 'زيارات مسجلة' }}
        </span>
    </div>
    <div class="card-body p-4 bg-light-subtle">
        @forelse($registration->dailyReviews as $reviewIndex => $review)
            <div class="card border-0 shadow-xs rounded-3 mb-3 bg-white overflow-hidden">
                <!-- Header -->
                <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white px-3 py-1.5 fs-8 fw-bold rounded-pill">
                            زيارة #{{ $registration->dailyReviews->count() - $reviewIndex }}
                        </span>

                        {{-- Distinguish Daily Review vs Scheduled Review --}}
                        @if($review->WC_DREV_SCHEDULED_REVIEW_CD || $review->scheduledReview)
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 fs-8 rounded-pill fw-bold">
                                <i class="bi bi-calendar-check-fill me-1"></i>مراجعة مجدولة
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-8 rounded-pill fw-bold">
                                <i class="bi bi-sun-fill me-1"></i>مراجعة يومية
                            </span>
                        @endif

                        <span class="fw-bold text-dark fs-7">
                            <i class="bi bi-calendar3 me-1 text-primary"></i>{{ \Carbon\Carbon::parse($review->WC_DREV_VISIT_DATE)->format('Y-m-d H:i') }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- Overall Status Badge -->
                        @if($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_SCHEDULED')
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-calendar-check-fill me-1"></i>تحسن وحجز مراجعة جديدة
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_DISCHARGE')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i>تحسن بدون مراجعة جديدة
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_SCHEDULED')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>تدهور مع مراجعة جديدة
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_ADMISSION')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-hospital-fill me-1"></i>تدهور مع تسجيل دخول للمستشفى
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_STAY')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-graph-up-arrow me-1"></i>تحسن مع استمرار
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_STAY')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-arrow-repeat me-1"></i>تدهور (مستمر بالعلاج)
                            </span>
                        @elseif($review->WC_DREV_OVERALL_STATUS === 'DEATH')
                            <span class="badge bg-secondary text-white px-3 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-x-circle-fill me-1"></i>حالة وفاة
                            </span>
                        @else
                            <span class="badge bg-light text-dark border px-3 py-1.5 fs-8 rounded-pill">
                                {{ $review->WC_DREV_OVERALL_STATUS }}
                            </span>
                        @endif

                        @if($review->scheduledReview)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-8 rounded-pill">
                                <i class="bi bi-calendar-plus me-1"></i>موعد مجدول: {{ \Carbon\Carbon::parse($review->scheduledReview->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body p-4">
                    <!-- Audit info -->
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-8 mb-3 pb-3 border-bottom">
                        <span>
                            <i class="bi bi-person-badge text-primary me-1"></i>الممرض المباشر: <strong>{{ $review->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
                        </span>
                        <span>&bull;</span>
                        <span>
                            <i class="bi bi-pencil-square text-primary me-1"></i>مُدخل البيانات: <strong>{{ $review->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</strong>
                        </span>
                    </div>

                    @if($review->WC_DREV_GENERAL_NOTES)
                        <div class="mb-3 bg-light-subtle p-3 rounded-3 border border-secondary-subtle">
                            <span class="d-block fw-bold fs-7 text-dark mb-1"><i class="bi bi-journal-text me-1 text-primary"></i>الملاحظات العامة للزيارة:</span>
                            <p class="fs-7 text-dark mb-0 lh-base" style="white-space: pre-line;">{{ $review->WC_DREV_GENERAL_NOTES }}</p>
                        </div>
                    @endif

                    <!-- Per-Wound Details Grid -->
                    <h6 class="fw-bold fs-7 text-dark mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-bandaid text-primary me-1"></i>تقييم الجروح في هذه الزيارة:
                    </h6>
                    <div class="row g-3">
                        @foreach($review->details as $detail)
                            <div class="col-12 col-md-6">
                                <div class="p-3 border rounded-3 bg-light-subtle h-100 shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                        <span class="fw-bold text-dark fs-7">
                                            <i class="bi bi-patch-minus text-primary me-1"></i>
                                            {{ $detail->regDiagnosis?->woundType?->WC_WTYPE_NAME ?? 'جرح' }} - <span class="text-primary">{{ $detail->regDiagnosis?->diagnosis?->WC_DIAG_NAME }}</span>
                                        </span>
                                        
                                        @if($detail->WC_DREVD_OUTCOME_STATUS === 'IMPROVED_FOLLOWUP')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fs-8 rounded-pill">تحسن - متابعة</span>
                                        @elseif($detail->WC_DREVD_OUTCOME_STATUS === 'IMPROVED_DISCHARGE')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-8 rounded-pill">مكتمل الشفاء</span>
                                        @elseif($detail->WC_DREVD_OUTCOME_STATUS === 'WORSENED')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fs-8 rounded-pill">تراجع / زيادة إفرازات</span>
                                        @elseif($detail->WC_DREVD_OUTCOME_STATUS === 'DECEASED')
                                            <span class="badge bg-dark text-white px-2.5 py-1 fs-8 rounded-pill">تلف أنسجة شديد</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2.5 py-1 fs-8 rounded-pill">{{ $detail->WC_DREVD_OUTCOME_STATUS }}</span>
                                        @endif
                                    </div>
                                    <ul class="list-unstyled fs-7 text-muted mb-0">
                                        <li class="mb-1">
                                            <strong class="text-dark fs-8">الضمادة المطبقة:</strong> 
                                            <span class="text-dark">{{ $detail->dressing?->WC_DRESS_NAME ?? 'لم تحدد' }}</span>
                                        </li>
                                        <li class="mb-1">
                                            <strong class="text-dark fs-8">العلاج الموضعي:</strong> 
                                            <span class="text-dark">{{ $detail->medication?->WC_MED_NAME ?? 'لم يحدد' }}</span>
                                        </li>
                                        @if($detail->WC_DREVD_OBSERVATION)
                                            <li>
                                                <strong class="text-dark fs-8">الملاحظة السريرية:</strong> 
                                                <span class="text-dark">{{ $detail->WC_DREVD_OBSERVATION }}</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded-3 border">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 mb-3">
                    <i class="bi bi-clipboard2-x fs-2"></i>
                </div>
                <h5 class="fw-bold fs-6 mb-1 text-dark">لا توجد زيارات تقييم يومية مسجلة سابقاً</h5>
                <p class="fs-7 text-muted mb-0">قم بالنقر على زر "+ إضافة مراجعة يومية" لتوثيق الزيارة اليومية الأولى للمريض.</p>
            </div>
        @endforelse
    </div>
</div>

