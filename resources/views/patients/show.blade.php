@extends('layouts.app')

@section('title', 'السجل الطبي - ' . $patient->MR_PAT_FULL_NAME)

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs rounded-3">
                <i class="bi bi-arrow-right"></i>
                <span>العودة للبحث</span>
            </a>
            <div>
                <h1 class="fw-bolder fs-4 mb-0 text-dark">
                    <i class="bi bi-person-lines-fill text-primary me-1"></i>السجل الطبي الشامل للمريض
                </h1>
            </div>
        </div>
    </div>

    @include('patients.partials.patient_demographics_banner', ['selectedPatient' => $patient])

    <h3 class="fw-bold fs-5 text-dark mb-3 mt-4 d-flex align-items-center gap-2">
        <i class="bi bi-layers-half text-primary"></i>سجلات التسجيلات السريرية ({{ $patient->registrations->count() }})
    </h3>

    <div class="row g-4">
        @forelse($patient->registrations as $regIndex => $reg)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden registration-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 border-bottom pb-3 mb-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary text-white fs-8 rounded-pill px-3 py-1">
                                        تسجيل سريري رقم #{{ $patient->registrations->count() - $regIndex }}
                                    </span>
                                    <span class="badge {{ $reg->WC_REG_STATUS === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }} fs-8 rounded-pill px-3 py-1">
                                        {{ $reg->WC_REG_STATUS === 'ACTIVE' ? 'نشط (مفتوح)' : 'مُخرج (مغلق)' }}
                                    </span>
                                    <span class="text-muted fs-8 fw-bold ms-2">
                                        <i class="bi bi-hash text-primary"></i>{{ $reg->WC_REG_CODE }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-3 text-dark fs-7">
                                    <span>
                                        <i class="bi bi-calendar-plus text-primary me-1"></i>تاريخ التسجيل: <strong>{{ \Carbon\Carbon::parse($reg->WC_REG_DATE)->format('Y-m-d H:i') }}</strong>
                                    </span>
                                    @if($reg->WC_REG_STATUS === 'DISCHARGED')
                                        <span>
                                            <i class="bi bi-calendar-check text-secondary me-1"></i>تاريخ الخروج: <strong>{{ \Carbon\Carbon::parse($reg->WC_REG_DISCHARGED_AT)->format('Y-m-d H:i') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($reg->WC_REG_STATUS === 'ACTIVE')
                                <a href="{{ route('registrations.daily-reviews.index', $reg->WC_REG_CODE) }}" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>إضافة مراجعة جديدة</span>
                                </a>
                            @endif
                        </div>

                        <!-- Registration Summary Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-light-subtle rounded-3 border h-100">
                                    <span class="d-block fs-8 text-muted mb-1"><i class="bi bi-building text-primary me-1"></i>الجهة المحيلة</span>
                                    <strong class="d-block text-dark fs-7">{{ $reg->department?->WC_DEPT_NAME ?? 'غير محدد' }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-light-subtle rounded-3 border h-100">
                                    <span class="d-block fs-8 text-muted mb-1"><i class="bi bi-bandaid text-primary me-1"></i>الجروح المسجلة ({{ $reg->diagnoses->count() }})</span>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @forelse($reg->diagnoses as $diag)
                                            <span class="badge bg-white text-dark border px-2 py-1 fs-8">
                                                {{ $diag->diagnosis?->WC_DIAG_NAME }}
                                            </span>
                                        @empty
                                            <span class="text-muted fs-8 italic">لا يوجد تشخيص</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-light-subtle rounded-3 border h-100">
                                    <span class="d-block fs-8 text-muted mb-1"><i class="bi bi-person-badge text-primary me-1"></i>كادر التمريض</span>
                                    <span class="d-block text-dark fs-7">تسجيل: <strong>{{ $reg->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- REVIEWS SLIDER SECTION -->
                        <div class="reviews-section mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold fs-6 text-dark mb-0">
                                    <i class="bi bi-clock-history text-primary me-1"></i>السجل الطبي والمراجعات السابقة
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-scroll-left" data-target="#slider-{{ $reg->WC_REG_CODE }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-scroll-right" data-target="#slider-{{ $reg->WC_REG_CODE }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                </div>
                            </div>

                            @if($reg->dailyReviews->count() > 0)
                                <!-- Horizontal Scroll Slider -->
                                <div class="reviews-slider d-flex gap-3 overflow-x-auto pb-3 px-1 snap-x" id="slider-{{ $reg->WC_REG_CODE }}" style="scroll-behavior: smooth;">
                                    @foreach($reg->dailyReviews as $reviewIndex => $review)
                                        <div class="review-slide card border shadow-xs rounded-4 flex-shrink-0 snap-center transition-all bg-light-subtle" style="width: 380px;">
                                            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-primary text-white px-2 py-1 fs-8 fw-bold align-self-start rounded-pill">
                                                        زيارة #{{ $reg->dailyReviews->count() - $reviewIndex }}
                                                    </span>
                                                    <span class="fw-bold text-dark fs-7 mt-1">
                                                        <i class="bi bi-calendar3 text-primary me-1"></i>{{ \Carbon\Carbon::parse($review->WC_DREV_VISIT_DATE)->format('Y-m-d') }}
                                                    </span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-light border rounded-circle toggle-zoom-btn" title="تكبير/تصغير المراجعة">
                                                    <i class="bi bi-arrows-angle-expand text-secondary"></i>
                                                </button>
                                            </div>
                                            <div class="card-body p-4 slide-content">
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <span class="d-block fs-8 text-muted mb-1">النتيجة:</span>
                                                    <strong class="d-block text-dark fs-7">
                                                        @if($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_SCHEDULED')
                                                            <i class="bi bi-calendar-check-fill text-info me-1"></i>تحسن ومراجعة
                                                        @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_DISCHARGE')
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i>تحسن بدون مراجعة
                                                        @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_SCHEDULED' || $review->WC_DREV_OVERALL_STATUS === 'WORSENED_ADMISSION' || $review->WC_DREV_OVERALL_STATUS === 'WORSENED_STAY')
                                                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>تدهور
                                                        @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_STAY')
                                                            <i class="bi bi-graph-up-arrow text-success me-1"></i>تحسن مستمر
                                                        @else
                                                            {{ $review->WC_DREV_OVERALL_STATUS }}
                                                        @endif
                                                    </strong>
                                                </div>
                                                
                                                <div class="d-flex flex-column gap-3 wound-details">
                                                    @foreach($review->details as $detail)
                                                        <div class="p-2 border rounded-3 bg-white">
                                                            <div class="fw-bold text-dark fs-8 border-bottom pb-1 mb-2">
                                                                <i class="bi bi-patch-minus text-primary me-1"></i>{{ $detail->regDiagnosis?->diagnosis?->WC_DIAG_NAME }}
                                                            </div>
                                                            <div class="fs-8 text-muted">
                                                                <div class="mb-1"><strong>الضماد:</strong> {{ $detail->dressing?->WC_DRESS_NAME ?? 'بدون' }}</div>
                                                                <div><strong>العلاج:</strong> {{ $detail->medication?->WC_MED_NAME ?? 'بدون' }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-clipboard-x fs-3 text-muted d-block mb-2"></i>
                                    <h6 class="text-secondary fw-bold">لا توجد مراجعات مسجلة</h6>
                                    <p class="text-muted fs-8 mb-0">لم يتم تسجيل أي مراجعات يومية أو مجدولة لهذا التسجيل بعد.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
                    <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold text-dark mb-1">لا توجد تسجيلات سريرية لهذا المريض</h5>
                    <p class="text-muted fs-7 mb-0">المريض موجود بالنظام ولكن لم يتم إنشاء أي جلسة تسجيل بالعيادة له بعد.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>

<style>
    /* Horizontal Slider Styles */
    .reviews-slider {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE and Edge */
        scroll-snap-type: x mandatory;
    }
    .reviews-slider::-webkit-scrollbar {
        display: none; /* Chrome, Safari and Opera */
    }
    .snap-x {
        scroll-snap-type: x mandatory;
    }
    .snap-center {
        scroll-snap-align: center;
    }
    
    /* Expanded (Zoomed) State for Review Slides */
    .review-slide {
        transition: all 0.3s ease;
    }
    .review-slide.expanded {
        width: 100% !important;
        position: relative;
        z-index: 10;
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
        transform: scale(1.02);
    }
    .review-slide.expanded .wound-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Slider scroll buttons
        const scrollLeftBtns = document.querySelectorAll('.btn-scroll-left');
        const scrollRightBtns = document.querySelectorAll('.btn-scroll-right');

        scrollLeftBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.getAttribute('data-target'));
                if (target) target.scrollBy({ left: -400, behavior: 'smooth' });
            });
        });

        scrollRightBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.getAttribute('data-target'));
                if (target) target.scrollBy({ left: 400, behavior: 'smooth' });
            });
        });

        // Expand / Collapse (Zoom) Toggle
        const toggleZoomBtns = document.querySelectorAll('.toggle-zoom-btn');
        toggleZoomBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const slide = this.closest('.review-slide');
                const isExpanded = slide.classList.contains('expanded');
                
                // If opening this one, close others in the same slider
                if (!isExpanded) {
                    const slider = slide.closest('.reviews-slider');
                    slider.querySelectorAll('.review-slide.expanded').forEach(s => {
                        s.classList.remove('expanded');
                        s.querySelector('.toggle-zoom-btn i').classList.replace('bi-arrows-angle-contract', 'bi-arrows-angle-expand');
                    });
                }

                // Toggle current
                slide.classList.toggle('expanded');
                
                // Toggle icon
                const icon = this.querySelector('i');
                if (slide.classList.contains('expanded')) {
                    icon.classList.replace('bi-arrows-angle-expand', 'bi-arrows-angle-contract');
                    // Scroll to this slide so it's centered when expanded
                    slide.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    icon.classList.replace('bi-arrows-angle-contract', 'bi-arrows-angle-expand');
                }
            });
        });
    });
</script>
@endsection
