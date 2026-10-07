@php
    $variant = $variant ?? 'pending';
    $isPending = $variant === 'pending';
    $colorClass = $isPending ? 'warning' : 'success';
    $iconClass = $isPending ? 'bi-hourglass-split' : 'bi-check-circle-fill';
    $statusText = $isPending ? 'بانتظار التقييم اليوم' : 'تم التقييم اليوم';
@endphp

<div class="col-12 col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white patient-card transition-all overflow-hidden position-relative border-top border-4 border-{{ $colorClass }}">
        <div class="card-body p-4 d-flex flex-column justify-content-between">
            <div>
                <!-- Header info -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-{{ $colorClass }} bg-opacity-10 text-{{ $colorClass }} d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h5 class="fw-bolder fs-6 mb-0 text-dark">{{ $reg->patient?->MR_PAT_FULL_NAME }}</h5>
                            <span class="fs-8 text-muted">رقم الهوية: {{ $reg->patient?->MR_PAT_ID_NO }}</span>
                        </div>
                    </div>
                    <span class="badge bg-{{ $colorClass }}-subtle text-{{ $colorClass }} border border-{{ $colorClass }}-subtle px-2.5 py-1 fs-8 rounded-pill">
                        @if(!$isPending)<i class="bi {{ $iconClass }} me-1"></i>@endif{{ $statusText }}
                    </span>
                </div>

                @if($isPending)
                    <!-- Patient Metadata -->
                    <div class="p-3 bg-light-subtle rounded-3 border mb-3">
                        <div class="row g-2 fs-8 text-muted">
                            <div class="col-6">
                                <span><i class="bi bi-building me-1 text-primary"></i>القسم:</span>
                                <strong class="d-block text-dark fs-8">{{ $reg->department?->WC_DEPT_NAME ?? 'عيادة الجروح' }}</strong>
                            </div>
                            <div class="col-6">
                                <span><i class="bi bi-person-badge me-1 text-primary"></i>الممرض:</span>
                                <strong class="d-block text-dark fs-8">{{ $reg->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnoses / Wounds Pills -->
                    <div class="mb-3">
                        <span class="d-block fs-8 text-muted fw-bold mb-1"><i class="bi bi-bandaid text-primary me-1"></i>الجروح المسجلة ({{ $reg->diagnoses->count() }}):</span>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($reg->diagnoses as $diag)
                                <span class="badge bg-white text-dark border px-2.5 py-1 fs-8 rounded-pill">
                                    {{ $diag->woundType?->WC_WTYPE_NAME }} - {{ $diag->diagnosis?->WC_DIAG_NAME }}
                                </span>
                            @empty
                                <span class="text-muted fs-8 italic">لا توجد جروح محددة</span>
                            @endforelse
                        </div>
                    </div>
                @else
                    <!-- Latest Review Info -->
                    @php
                        $latestReview = $reg->dailyReviews->first();
                    @endphp
                    @if($latestReview)
                        <div class="p-3 bg-success-subtle bg-opacity-20 rounded-3 border border-success-subtle mb-3">
                            <span class="d-block fs-8 text-muted mb-1">وقت التقييم اليوم:</span>
                            <strong class="d-block fs-7 text-dark">
                                <i class="bi bi-clock text-success me-1"></i>
                                {{ \Carbon\Carbon::parse($latestReview->WC_DREV_VISIT_DATE)->format('H:i - Y-m-d') }}
                            </strong>
                            <span class="fs-8 text-muted mt-1 d-block">
                                بواسطة الممرض: <strong>{{ $latestReview->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
                            </span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Card Footer Action -->
            <div class="pt-3 border-top mt-2">
                @if($isPending)
                    <a href="{{ route('reviews.daily.create', $reg->WC_REG_CODE) }}" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold shadow-2xs">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>إدخال مراجعة اليوم</span>
                    </a>
                @else
                    <a href="{{ route('patients.show', $reg->WC_REG_PATIENT_CD) }}" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fs-7">
                        <i class="bi bi-eye"></i>
                        <span>عرض التاريخ الصحي</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
