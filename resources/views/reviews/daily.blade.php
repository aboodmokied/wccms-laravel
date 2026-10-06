@extends('layouts.app')

@section('title', 'التقييم والزيارة اليومية للجروح - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <!-- BREADCRUMB & PAGE ACTION BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('reviews.daily') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs rounded-3">
                <i class="bi bi-grid-fill"></i>
                <span>جدول مراجعات اليوم</span>
            </a>
            <a href="{{ route('registrations.show', $registration->WC_REG_CODE) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs rounded-3">
                <i class="bi bi-arrow-right"></i>
                <span>ملف التسجيل</span>
            </a>
            <div>
                <h1 class="fw-bold fs-5 mb-0 text-dark">التقييم والزيارة اليومية للجروح</h1>
                <span class="text-muted fs-7">كود التسجيل: <code class="text-primary fw-bold">{{ $registration->WC_REG_CODE }}</code></span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($registration->WC_REG_STATUS === 'ACTIVE' || (isset($scheduledReview) && $scheduledReview))
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-2 fw-bold" data-bs-toggle="modal" data-bs-target="#addDailyReviewModal">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>{{ isset($scheduledReview) && $scheduledReview ? 'توثيق الموعد المجدول' : '+ إضافة زيارة تقييم يومي' }}</span>
                </button>
            @else
                <button type="button" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-2" disabled>
                    <i class="bi bi-lock-fill"></i>
                    <span>مُخرج (مغلق)</span>
                </button>
            @endif
        </div>
    </div>

    <!-- SCHEDULED REVIEW FULFILLMENT BANNER -->
    @if(isset($scheduledReview) && $scheduledReview)
        <div class="alert alert-info d-flex align-items-center justify-content-between gap-3 shadow-2xs border border-info-subtle mb-4 p-3 rounded-3" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-calendar-check-fill fs-3 text-info"></i>
                <div>
                    <strong class="d-block text-dark fs-7">جاري توثيق موعد مراجعة مجدولة (زيارة رقم #{{ $scheduledReview->WC_SREV_VISIT_SERIAL }})</strong>
                    <span class="fs-7 text-muted">تاريخ الموعد المجدول: {{ \Carbon\Carbon::parse($scheduledReview->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') }}</span>
                </div>
            </div>
            @if($registration->WC_REG_STATUS === 'ACTIVE')
                <button type="button" class="btn btn-info btn-sm px-3 fw-bold text-white shadow-2xs" data-bs-toggle="modal" data-bs-target="#addDailyReviewModal">
                    <i class="bi bi-clipboard-check me-1"></i>توثيق الموعد الآن
                </button>
            @endif
        </div>
    @endif

    <!-- DISCHARGED ALERT BANNER -->
    @if($registration->WC_REG_STATUS === 'DISCHARGED')
        <div class="alert alert-secondary d-flex align-items-center gap-3 shadow-2xs border-0 mb-4 p-3 rounded-3" role="alert">
            <i class="bi bi-info-circle-fill fs-4 text-secondary"></i>
            <div>
                <strong class="d-block text-dark fs-7">تنبيه: ملف التسجيل مخرج (DISCHARGED)</strong>
                <span class="fs-7 text-muted">تم تخريج هذا المريض من العيادة بتاريخ {{ \Carbon\Carbon::parse($registration->WC_REG_DISCHARGED_AT)->format('Y-m-d H:i') }}. السجلات والزيارات اليومية متاحة في وضع القراءة فقط لحفظ التوثيق التاريخي.</span>
            </div>
        </div>
    @endif

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-2xs border-0 mb-4 rounded-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-2xs border-0 mb-4 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <!-- 1. PATIENT SUMMARY BANNER -->
    @include('reviews.partials.patient_summary')

    <!-- 2. MAIN WORKSPACE NAVIGATION TABS -->
    <div class="mb-4">
        <ul class="nav nav-pills bg-white p-1.5 rounded-3 shadow-xs border gap-2" id="dailyWorkspaceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold fs-7 px-4 py-2.5 rounded-2 d-flex align-items-center gap-2"
                        id="timeline-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#timeline-pane"
                        type="button"
                        role="tab"
                        aria-controls="timeline-pane"
                        aria-selected="true">
                    <i class="bi bi-clock-history fs-6"></i>
                    <span>سجل الزيارات والتقييمات اليومية</span>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 fs-8">{{ $registration->dailyReviews->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold fs-7 px-4 py-2.5 rounded-2 d-flex align-items-center gap-2"
                        id="wounds-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#wounds-pane"
                        type="button"
                        role="tab"
                        aria-controls="wounds-pane"
                        aria-selected="false">
                    <i class="bi bi-bandaid fs-6"></i>
                    <span>الجروح النشطة المسجلة</span>
                    <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5 fs-8">{{ $registration->diagnoses->count() }}</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB PANES -->
    <div class="tab-content" id="dailyWorkspaceTabContent">
        <!-- PANE 1: DAILY REVIEWS HISTORY TIMELINE -->
        <div class="tab-pane fade show active" id="timeline-pane" role="tabpanel" aria-labelledby="timeline-tab">
            @include('reviews.partials.review_timeline')
        </div>

        <!-- PANE 2: ACTIVE WOUNDS OVERVIEW -->
        <div class="tab-pane fade" id="wounds-pane" role="tabpanel" aria-labelledby="wounds-tab">
            @include('reviews.partials.wound_tabs')
        </div>
    </div>

</div>

<!-- 3. MULTI-WOUND DAILY REVIEW MODAL (ACTIVE REGISTRATION OR SCHEDULED REVIEW FULFILLMENT) -->
@if($registration->WC_REG_STATUS === 'ACTIVE' || (isset($scheduledReview) && $scheduledReview))
    @include('reviews.partials.daily_review_modal')
@endif

@endsection

