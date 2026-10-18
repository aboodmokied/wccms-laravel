@extends('layouts.app')

@section('title', 'الملف السريري للمريض')

@push('styles')
    @vite(['resources/css/pages/registrations.css'])
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- BREADCRUMB & PAGE ACTION BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('registrations.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm rounded-2">
                <i class="bi bi-arrow-right"></i>
                <span>المرضى المسجلون</span>
            </a>
            <div>
                <h1 class="wccms-page-heading mb-1">الملف السريري للمريض</h1>
                <span class="text-muted">بيانات التسجيل، الجروح، الصور، والمراجعات في صفحة واحدة.</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($registration->WC_REG_STATUS === 'ACTIVE')
                <a href="{{ route('reviews.daily.create', $registration->WC_REG_CODE) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-2">
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span>إدخال مراجعة يومية</span>
                </a>
            @endif
            <a href="{{ route('patients.show', $registration->WC_REG_PATIENT_CD) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3 rounded-2">
                <i class="bi bi-layers"></i>
                <span>كل تسجيلات المريض</span>
            </a>

            @if($registration->WC_REG_STATUS === 'ACTIVE')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7 me-1 rounded-pill">
                    <i class="bi bi-circle-fill me-1 fs-8"></i>تسجيل نشط (ACTIVE)
                </span>
                <button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-2" data-bs-toggle="modal" data-bs-target="#dischargeModal">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>تخريج المريض</span>
                </button>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-7 me-1 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1 fs-8"></i>مُخرج (DISCHARGED)
                </span>
                <span class="text-muted fs-7">
                    تاريخ التخريج: <strong>{{ \Carbon\Carbon::parse($registration->WC_REG_DISCHARGED_AT)->format('Y-m-d H:i') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <!-- HERO PATIENT SUMMARY BANNER -->
    <div class="card border-0 shadow-sm mb-3 clinical-patient-hero">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-xs" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="fw-bold fs-5 mb-0 text-dark">{{ $registration->patient?->MR_PAT_FULL_NAME }}</h2>
                        <span class="badge bg-primary text-white px-2.5 py-1 fs-8 rounded-pill">
                            <i class="bi bi-card-text me-1"></i>{{ $registration->patient?->MR_PAT_ID_NO }}
                        </span>
                        @if($registration->is_reviewed_today)
                            <span class="badge bg-success text-white px-2.5 py-1 fs-8 rounded-pill">
                                <i class="bi bi-calendar-check me-1"></i>تمت مراجعته اليوم
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-2.5 py-1 fs-8 rounded-pill">
                                <i class="bi bi-clock-history me-1"></i>لم يراجع اليوم
                            </span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-7">
                        <span><i class="bi bi-gender-ambiguous me-1 text-primary"></i>الجنس: <strong>{{ $registration->patient?->MR_PAT_GENDER === 'MALE' ? 'ذكر' : 'أنثى' }}</strong></span>
                        <span>&bull;</span>
                        <span><i class="bi bi-calendar3 me-1 text-primary"></i>تاريخ التسجيل: <strong>{{ \Carbon\Carbon::parse($registration->WC_REG_DATE)->format('Y-m-d H:i') }}</strong></span>
                        <span>&bull;</span>
                        <span><i class="bi bi-hospital me-1 text-primary"></i>القسم: <strong>{{ $registration->department?->WC_DEPT_NAME ?? 'عيادة الجروح' }}</strong></span>
                        <span>&bull;</span>
                        <span>
                            <i class="bi bi-calendar2-pulse me-1 text-primary"></i>حالة اليوم:
                            @if($registration->is_reviewed_today)
                                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>تمت المراجعة ({{ \Carbon\Carbon::parse($registration->today_review?->WC_DREV_VISIT_DATE)->format('H:i') }})</strong>
                            @else
                                <strong class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>لم تُجرَ مراجعة اليوم</strong>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="row g-2">
                        <div class="col-6 col-sm-3 col-xl-6">
                            <div class="clinical-summary-stat">
                                <span>المراجعات</span>
                                <strong>{{ $healthSummary['reviewsCount'] }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3 col-xl-6">
                            <div class="clinical-summary-stat">
                                <span>الجروح المسجلة</span>
                                <strong>{{ $registration->diagnoses->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3 col-xl-6">
                            <div class="clinical-summary-stat">
                                <span>آخر مراجعة</span>
                                <strong class="clinical-summary-date">{{ $healthSummary['lastReview'] ? \Illuminate\Support\Carbon::parse($healthSummary['lastReview']->WC_DREV_VISIT_DATE)->format('Y-m-d') : 'لا توجد' }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3 col-xl-6">
                            <div class="clinical-summary-stat">
                                <span>الموعد القادم</span>
                                <strong class="clinical-summary-date">{{ $healthSummary['nextScheduledReview'] ? \Illuminate\Support\Carbon::parse($healthSummary['nextScheduledReview']->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') : 'لا يوجد' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="clinical-record-nav mb-4" aria-label="أقسام الملف السريري">
        <a href="#clinical-details"><i class="bi bi-person-vcard" aria-hidden="true"></i>بيانات الحالة</a>
        <a href="#review-history"><i class="bi bi-clock-history" aria-hidden="true"></i>سجل المراجعات <span>{{ $healthSummary['reviewsCount'] }}</span></a>
    </nav>

    <!-- MAIN TWO-COLUMN CONTENT LAYOUT -->
    <div class="row g-4 mb-4" id="clinical-details">
        <!-- LEFT COLUMN: DETAILED INFO CARDS -->
        <div class="col-12 col-lg-4">
            <!-- 1. Patient Demographics & Contact Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-person-vcard text-primary fs-5"></i>
                    <h3 class="card-title fs-6 mb-0 fw-bold text-dark">البيانات الشخصية للمريض</h3>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush fs-7">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-person me-2"></i>الاسم الرباعي:</span>
                            <span class="fw-bold text-dark">{{ $registration->patient?->MR_PAT_FULL_NAME }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-credit-card-2-front me-2"></i>رقم الهوية:</span>
                            <span class="fw-bold text-dark font-monospace">{{ $registration->patient?->MR_PAT_ID_NO }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-telephone me-2"></i>رقم الهاتف:</span>
                            <span class="fw-semibold text-dark">{{ $registration->patient?->MR_PAT_PHONE ?: 'غير متوفر' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>تاريخ الميلاد:</span>
                            <span class="fw-semibold text-dark">{{ $registration->patient?->MR_PAT_DOB ? \Carbon\Carbon::parse($registration->patient->MR_PAT_DOB)->format('Y-m-d') : 'غير محدد' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 2. Registration Audit Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard2-check text-success fs-5"></i>
                    <h3 class="card-title fs-6 mb-0 fw-bold text-dark">معلومات التسجيل والتدقيق</h3>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush fs-7">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-building me-2"></i>القسم المستقبِل:</span>
                            <span class="fw-bold text-primary">{{ $registration->department?->WC_DEPT_NAME ?? 'عيادة الجروح' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-person-badge me-2"></i>الممرض المسؤول:</span>
                            <span class="fw-semibold text-dark">{{ $registration->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</span>
                        </li>
                        @if($registration->procedure)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <span class="text-muted"><i class="bi bi-activity me-2"></i>الإجراء المتبع:</span>
                                <span class="fw-semibold text-dark">{{ $registration->procedure->WC_PROC_NAME }}</span>
                            </li>
                        @endif
                        @if($registration->WC_REG_STATUS === 'DISCHARGED')
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <span class="text-muted"><i class="bi bi-person-x me-2"></i>الممرض المنفذ للتخريج:</span>
                                <span class="fw-semibold text-danger">{{ $registration->dischargingNurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($registration->WC_REG_PMH_NOTES)
                <!-- 3. Medical History / PMH Notes Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-warning fs-5"></i>
                        <h3 class="card-title fs-6 mb-0 fw-bold text-dark">ملاحظات التاريخ المرضي (PMH)</h3>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-dark fs-7 mb-0 lh-base" style="white-space: pre-line;">{{ $registration->WC_REG_PMH_NOTES }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- RIGHT COLUMN: WOUND DIAGNOSES & CLINICAL RECORDS -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-journal-medical text-primary fs-5"></i>
                        <h3 class="card-title fs-6 mb-0 fw-bold text-dark">تشخيصات الجروح التابعة للملف</h3>
                    </div>
                    @if($registration->WC_REG_STATUS === 'ACTIVE')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-3 rounded-2" data-bs-toggle="modal" data-bs-target="#addWoundModal">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>إضافة تشخيص جرح</span>
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle border-bottom">
                                <tr class="text-muted fw-bold fs-7">
                                    <th class="ps-4">#</th>
                                    <th>نوع الجرح (Wound Type)</th>
                                    <th>التشخيص السريري (Diagnosis)</th>
                                    <th>الممرض الفاحص</th>
                                    <th class="pe-4 text-end">التوثيق بواسطة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registration->diagnoses as $index => $diag)
                                    <tr>
                                        <td class="ps-4 text-muted fs-7 fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                                                <i class="bi bi-bandaid me-1"></i>{{ $diag->woundType?->WC_WTYPE_NAME ?? 'غير محدد' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark fs-7">
                                                {{ $diag->diagnosis?->WC_DIAG_NAME ?? 'غير محدد' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-secondary fs-7">
                                                <i class="bi bi-person me-1"></i>{{ $diag->clinicalEmployee?->PER_EMP_FULL_NAME ?? 'غير محدد' }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <span class="badge bg-light text-dark border px-2.5 py-1 fs-8">
                                                {{ $diag->entryUser?->PER_USER_USERNAME ?? 'النظام' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4 text-muted">
                                                <i class="bi bi-bandaid fs-1 d-block mb-3 opacity-50 text-primary"></i>
                                                <h5 class="fw-bold fs-6 mb-1 text-dark">لا توجد تشخيصات جروح مسجلة لهذا المريض</h5>
                                                @if($registration->WC_REG_STATUS === 'ACTIVE')
                                                    <p class="fs-7 text-muted mb-3">قم بالنقر على زر "إضافة تشخيص جرح" لتوثيق الجرح والبدء بالخطة العلاجية.</p>
                                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm rounded-2" data-bs-toggle="modal" data-bs-target="#addWoundModal">
                                                        <i class="bi bi-plus-circle"></i>
                                                        <span>إضافة أول تشخيص جرح</span>
                                                    </button>
                                                @else
                                                    <p class="fs-7 text-muted mb-0">لا توجد تشخيصات جروح موثقة ضمن هذا التسجيل.</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-4"><h2 class="h5 fw-bold mb-1">التقييمات المنظمة حسب الجرح</h2><p class="text-muted small mb-0">خط الأساس والمتابعات بترتيب زمني واضح لكل جرح.</p></div>
        <div class="card-body p-4 d-grid gap-4">
            @forelse($registration->diagnoses as $diagnosis)
                <article><h3 class="h6 fw-bold">{{ $diagnosis->woundType?->WC_WTYPE_NAME ?? 'جرح غير محدد' }} — {{ $diagnosis->diagnosis?->WC_DIAG_NAME ?? 'دون تشخيص' }}</h3>
                    @forelse($diagnosis->assessments as $assessment)
                        @include('wound_assessments.partials.summary', compact('assessment'))
                    @empty
                        @include('wound_assessments.partials.summary', ['assessment' => null])
                    @endforelse
                </article>
            @empty
                <p class="text-muted mb-0">لا توجد جروح مسجلة.</p>
            @endforelse
        </div>
    </section>

    @include('reviews.partials.review_timeline')
</div>

@if($registration->WC_REG_STATUS === 'ACTIVE')
    <!-- Active-registration workflows -->
    @include('registrations.partials.wound_diagnosis_modal')
    @include('registrations.partials.discharge_modal')
@endif

@endsection
