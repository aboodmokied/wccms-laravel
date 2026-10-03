@extends('layouts.app')

@section('title', 'جدول الزيارات والمراجعات اليومية للجروح - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE HEADER BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="fw-bolder fs-3 mb-1 text-dark">جدول المراجعات والزيارات اليومية للجروح</h1>
            <p class="text-muted fs-7 mb-0">عرض وإدارة جميع المرضى المسجلين الذين يحتاجون تقييم وزيارة يومية</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7 rounded-pill fw-bold">
                <i class="bi bi-calendar-event me-1"></i>تاريخ اليوم: {{ now()->format('Y-m-d') }}
            </span>
        </div>
    </div>

    <!-- QUICK CLINICAL METRICS RIBBON -->
    <div class="row g-3 mb-4">
        <!-- Metric 1: Pending Today -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted fs-8 fw-bold mb-1">بانتظار المراجعة اليوم</span>
                        <h2 class="fw-bolder text-warning fs-3 mb-0">{{ $pendingTodayRegistrations->count() }}</h2>
                        <span class="fs-8 text-muted">مرضى لم تتم مراجعتهم بعد</span>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 2: Completed Today -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted fs-8 fw-bold mb-1">تمت مراجعتهم اليوم</span>
                        <h2 class="fw-bolder text-success fs-3 mb-0">{{ $completedTodayRegistrations->count() }}</h2>
                        <span class="fs-8 text-muted">زيارات تم توثيقها بنجاح</span>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Total Active Patients -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block text-muted fs-8 fw-bold mb-1">إجمالي الحالات النشطة</span>
                        <h2 class="fw-bolder text-primary fs-3 mb-0">{{ $pendingTodayRegistrations->count() + $completedTodayRegistrations->count() }}</h2>
                        <span class="fs-8 text-muted">مرضى مسجلون حالياً</span>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('reviews.daily') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control bg-light border-0 fs-7" placeholder="البحث باسم المريض أو رقم الهوية الوطنية...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2 rounded-2 fw-bold">بحث</button>
                    @if($search)
                        <a href="{{ route('reviews.daily') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-2">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN TABS (PENDING VS COMPLETED TODAY) -->
    <div class="mb-4">
        <ul class="nav nav-pills bg-white p-1.5 rounded-3 shadow-xs border gap-2" id="dailyIndexTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold fs-7 px-4 py-2.5 rounded-2 d-flex align-items-center gap-2"
                        id="pending-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#pending-pane"
                        type="button"
                        role="tab"
                        aria-controls="pending-pane"
                        aria-selected="true">
                    <i class="bi bi-hourglass-split text-warning fs-6"></i>
                    <span>مرضى لم تتم مراجعتهم بعد اليوم</span>
                    <span class="badge bg-warning text-dark rounded-pill px-2.5 py-0.5 fs-8">{{ $pendingTodayRegistrations->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold fs-7 px-4 py-2.5 rounded-2 d-flex align-items-center gap-2"
                        id="completed-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#completed-pane"
                        type="button"
                        role="tab"
                        aria-controls="completed-pane"
                        aria-selected="false">
                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                    <span>مرضى تمت مراجعتهم اليوم</span>
                    <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fs-8">{{ $completedTodayRegistrations->count() }}</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB PANES CONTENT -->
    <div class="tab-content" id="dailyIndexTabContent">
        
        <!-- PANE 1: PENDING TODAY -->
        <div class="tab-pane fade show active" id="pending-pane" role="tabpanel" aria-labelledby="pending-tab">
            @if($pendingTodayRegistrations->count() > 0)
                <div class="row g-3">
                    @foreach($pendingTodayRegistrations as $reg)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white patient-card transition-all overflow-hidden position-relative border-top border-4 border-warning">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <!-- Header info -->
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <h5 class="fw-bolder fs-6 mb-0 text-dark">{{ $reg->patient?->MR_PAT_FULL_NAME }}</h5>
                                                    <span class="fs-8 text-muted">رقم الهوية: {{ $reg->patient?->MR_PAT_ID_NO }}</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                بانتظار التقييم اليوم
                                            </span>
                                        </div>

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
                                    </div>

                                    <!-- Card Footer Action -->
                                    <div class="pt-3 border-top mt-2">
                                        <a href="{{ route('registrations.daily-reviews.index', $reg->WC_REG_CODE) }}" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold shadow-2xs">
                                            <i class="bi bi-plus-circle-fill"></i>
                                            <span>إجراء التقييم والزيارة اليومية</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center p-4 mb-3">
                        <i class="bi bi-emoji-smile fs-1"></i>
                    </div>
                    <h4 class="fw-bolder fs-5 text-dark mb-1">تم إجراء التقييم اليومي لجميع المرضى!</h4>
                    <p class="text-muted fs-7 mb-0">جميع المرضى المسجلين تمت زيارتهم ومراجعتهم بنجاح خلال تاريخ اليوم.</p>
                </div>
            @endif
        </div>

        <!-- PANE 2: COMPLETED TODAY -->
        <div class="tab-pane fade" id="completed-pane" role="tabpanel" aria-labelledby="completed-tab">
            @if($completedTodayRegistrations->count() > 0)
                <div class="row g-3">
                    @foreach($completedTodayRegistrations as $reg)
                        @php
                            $latestReview = $reg->dailyReviews->first();
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white patient-card transition-all overflow-hidden border-top border-4 border-success">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <!-- Header info -->
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <h5 class="fw-bolder fs-6 mb-0 text-dark">{{ $reg->patient?->MR_PAT_FULL_NAME }}</h5>
                                                    <span class="fs-8 text-muted">رقم الهوية: {{ $reg->patient?->MR_PAT_ID_NO }}</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i>تم التقييم اليوم
                                            </span>
                                        </div>

                                        <!-- Latest Review Info -->
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
                                    </div>

                                    <!-- Action -->
                                    <div class="pt-3 border-top mt-2">
                                        <a href="{{ route('registrations.daily-reviews.index', $reg->WC_REG_CODE) }}" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fs-7">
                                            <i class="bi bi-eye"></i>
                                            <span>عرض سجل التقييمات الكامل</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center p-4 mb-3">
                        <i class="bi bi-journal-x fs-1"></i>
                    </div>
                    <h4 class="fw-bolder fs-5 text-dark mb-1">لم تتم أي زيارات اليوم بعد</h4>
                    <p class="text-muted fs-7 mb-0">انتقل إلى تبويب "مرضى لم تتم مراجعتهم بعد" لبدء التقييم اليومي.</p>
                </div>
            @endif
        </div>

    </div>

</div>

<style>
    .patient-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .patient-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
</style>
@endsection
