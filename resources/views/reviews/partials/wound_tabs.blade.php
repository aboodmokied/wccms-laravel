<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bandaid text-primary fs-5"></i>
            <h3 class="card-title fs-6 mb-0 fw-bold text-dark">الجروح النشطة والتشخيصات المسجلة</h3>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fs-7 rounded-pill">
            {{ $registration->diagnoses->count() }} {{ $registration->diagnoses->count() === 1 ? 'جرح مسجل' : 'جروح مسجلة' }}
        </span>
    </div>
    <div class="card-body p-4 bg-light-subtle">
        @if($registration->diagnoses->count() > 0)
            {{-- Nav Tabs --}}
            <ul class="nav nav-pills bg-white p-1 rounded-3 border mb-3 gap-1" id="woundDiagnosisTabs" role="tablist">
                @foreach($registration->diagnoses as $idx => $diag)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-7 fw-bold rounded-2 px-3 py-2 {{ $idx === 0 ? 'active' : '' }}"
                                id="wound-tab-{{ $diag->WC_RDIAG_CODE }}"
                                data-bs-toggle="tab"
                                data-bs-target="#wound-pane-{{ $diag->WC_RDIAG_CODE }}"
                                type="button"
                                role="tab"
                                aria-controls="wound-pane-{{ $diag->WC_RDIAG_CODE }}"
                                aria-selected="{{ $idx === 0 ? 'true' : 'false' }}">
                            <i class="bi bi-patch-minus text-primary me-1"></i>
                            <span>جرح #{{ $idx + 1 }}: {{ $diag->woundType?->WC_WTYPE_NAME }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="woundDiagnosisTabContent">
                @foreach($registration->diagnoses as $idx => $diag)
                    @php
                        $latestDetail = $diag->latestDetail;
                    @endphp
                    <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}"
                         id="wound-pane-{{ $diag->WC_RDIAG_CODE }}"
                         role="tabpanel"
                         aria-labelledby="wound-tab-{{ $diag->WC_RDIAG_CODE }}">
                        <div class="p-4 border-0 rounded-3 bg-white shadow-xs">
                            <div class="row g-3">
                                {{-- Wound Type --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                        <span class="d-block text-muted fs-8 mb-1">
                                            <i class="bi bi-droplet text-primary me-1"></i>نوع الجرح
                                        </span>
                                        <span class="fw-bold text-dark fs-7">{{ $diag->woundType?->WC_WTYPE_NAME ?? 'غير محدد' }}</span>
                                    </div>
                                </div>

                                {{-- Diagnosis --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                        <span class="d-block text-muted fs-8 mb-1">
                                            <i class="bi bi-clipboard2-pulse text-primary me-1"></i>التشخيص السريري
                                        </span>
                                        <span class="fw-bold text-dark fs-7">{{ $diag->diagnosis?->WC_DIAG_NAME ?? 'غير محدد' }}</span>
                                    </div>
                                </div>

                                {{-- Current Dressing --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                        <span class="d-block text-muted fs-8 mb-1">
                                            <i class="bi bi-bandaid text-primary me-1"></i>الضمادة الحالية
                                        </span>
                                        @if($latestDetail && $latestDetail->dressing)
                                            <span class="fw-bold text-dark fs-7">{{ $latestDetail->dressing->WC_DRESS_NAME }}</span>
                                            <span class="d-block text-muted fs-8 mt-1">
                                                <i class="bi bi-clock me-1"></i>آخر تحديث: {{ \Carbon\Carbon::parse($latestDetail->dailyReview?->WC_DREV_VISIT_DATE)->format('Y-m-d') }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-7 fst-italic">لم تُطبق بعد</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Current Medication --}}
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                        <span class="d-block text-muted fs-8 mb-1">
                                            <i class="bi bi-capsule text-primary me-1"></i>العلاج الموضعي الحالي
                                        </span>
                                        @if($latestDetail && $latestDetail->medication)
                                            <span class="fw-bold text-dark fs-7">{{ $latestDetail->medication->WC_MED_NAME }}</span>
                                            <span class="d-block text-muted fs-8 mt-1">
                                                <i class="bi bi-clock me-1"></i>آخر تحديث: {{ \Carbon\Carbon::parse($latestDetail->dailyReview?->WC_DREV_VISIT_DATE)->format('Y-m-d') }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-7 fst-italic">لم يُطبق بعد</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Latest Outcome Status --}}
                                @if($latestDetail)
                                    <div class="col-12">
                                        <div class="p-3 border rounded-3 bg-primary-subtle bg-opacity-20 border-primary-subtle d-flex flex-wrap align-items-center gap-3">
                                            <span class="text-muted fs-8">
                                                <i class="bi bi-activity text-primary me-1"></i>آخر تقييم لحالة الجرح:
                                            </span>
                                            @if($latestDetail->WC_DREVD_OUTCOME_STATUS === 'IMPROVED_DISCHARGE')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-8 rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i>تحسن ممتاز - مكتمل الشفاء
                                                </span>
                                            @elseif($latestDetail->WC_DREVD_OUTCOME_STATUS === 'IMPROVED_FOLLOWUP')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 fs-8 rounded-pill">
                                                    <i class="bi bi-arrow-repeat me-1"></i>تحسن - يحتاج متابعة
                                                </span>
                                            @elseif($latestDetail->WC_DREVD_OUTCOME_STATUS === 'WORSENED')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 fs-8 rounded-pill">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>تراجع / زيادة إفرازات
                                                </span>
                                            @elseif($latestDetail->WC_DREVD_OUTCOME_STATUS === 'DECEASED')
                                                <span class="badge bg-dark text-white px-3 py-1.5 fs-8 rounded-pill">
                                                    <i class="bi bi-x-circle-fill me-1"></i>تلف أنسجة شديد
                                                </span>
                                            @endif

                                            @if($latestDetail->WC_DREVD_OBSERVATION)
                                                <span class="text-dark fs-8">
                                                    &bull; {{ Str::limit($latestDetail->WC_DREVD_OBSERVATION, 100) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Clinical employee who added the diagnosis --}}
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-8 pt-1">
                                        <span>
                                            <i class="bi bi-person-badge text-primary me-1"></i>الممرض الفاحص عند التسجيل:
                                            <strong>{{ $diag->clinicalEmployee?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
                                        </span>
                                        <span>&bull;</span>
                                        <span>
                                            <i class="bi bi-pencil-square text-primary me-1"></i>مُدخل البيانات:
                                            <strong>{{ $diag->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-bandaid fs-1 d-block mb-3 opacity-50 text-primary"></i>
                <h5 class="fw-bold fs-6 mb-1 text-dark">لم يتم تسجيل أي جروح لهذا المريض بعد</h5>
                <p class="fs-7 text-muted mb-0">يرجى إضافة تشخيص جرح من ملف التسجيل الرئيسي أولاً قبل بدء التقييم اليومي.</p>
            </div>
        @endif
    </div>
</div>

