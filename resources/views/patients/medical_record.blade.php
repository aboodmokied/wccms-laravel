@extends('layouts.app')

@section('title', 'السجل الطبي الشامل للمريض - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE TITLE ROW -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="fw-bolder fs-2 mb-1 text-dark">
                <i class="bi bi-file-earmark-medical-fill text-primary me-2"></i>السجل الطبي الشامل للمريض
            </h1>
            <p class="text-muted fs-6 mb-0">البحث برقم الهوية الوطنية أو الاسم، وعرض جميع التسجيلات والمراجعات اليومية والمجدولة الخاصة بالمريض.</p>
        </div>
    </div>

    <!-- SEARCH FORM CARD -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('patients.medical_record') }}" class="row g-3 align-items-center">
                <div class="col-12 col-md-9">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 fs-6"
                               placeholder="أدخل رقم الهوية الوطنية، رقم الملف، أو اسم المريض للبحث..."
                               value="{{ $search }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fs-6">
                        <i class="bi bi-search me-1"></i>بحث في السجلات
                    </button>
                    @if($search)
                        <a href="{{ route('patients.medical_record') }}" class="btn btn-outline-secondary btn-lg" title="إلغاء البحث">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($search !== '' && $patients->isEmpty())
        <!-- NO PATIENTS FOUND ALERT -->
        <div class="card border-0 shadow-sm mb-4 bg-warning bg-opacity-10 border-warning">
            <div class="card-body p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-1 d-block mb-2"></i>
                <h5 class="fw-bold text-dark mb-1">لم يتم العثور على مريض مطبق لهذا البحث</h5>
                <p class="text-muted fs-7 mb-0">تأكد من كتابة الاسم أو رقم الهوية بالشكل الصحيح ثم أعد المحاولة.</p>
            </div>
        </div>
    @elseif($search !== '' && $patients->count() > 1 && !$selectedPatient)
        <!-- MULTIPLE MATCHING PATIENTS SELECTION GRID -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h2 class="card-title fs-6 mb-0 fw-bold text-dark">
                    <i class="bi bi-people me-2 text-primary"></i>عُثر على عدة مرضى مطابقين لـ "{{ $search }}" (اختر مريضًا لتمرير السجل)
                </h2>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fs-7 rounded-pill">
                    {{ $patients->count() }} نتائج
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($patients as $p)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border h-100 shadow-2xs hover-shadow rounded-3">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <h5 class="fw-bold fs-6 mb-0 text-dark">{{ $p->MR_PAT_FULL_NAME }}</h5>
                                        </div>
                                        <ul class="list-unstyled fs-7 text-muted mb-3">
                                            <li><i class="bi bi-card-text me-1 text-primary"></i>الهوية: <strong class="text-dark">{{ $p->MR_PAT_ID_NO }}</strong></li>
                                            <li><i class="bi bi-telephone me-1 text-primary"></i>الهاتف: {{ $p->MR_PAT_PHONE ?: 'غير مسجل' }}</li>
                                            <li><i class="bi bi-gender-ambiguous me-1 text-primary"></i>الجنس: {{ $p->MR_PAT_GENDER === 'MALE' ? 'ذكر' : 'أنثى' }}</li>
                                        </ul>
                                    </div>
                                    <a href="{{ route('patients.medical_record', ['search' => $search, 'patient_code' => $p->MR_PAT_CODE]) }}"
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-folder2-open me-1"></i>عرض السجل الطبي
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($selectedPatient)
        <!-- SELECTED PATIENT DEMOGRAPHICS HERO BANNER -->
        <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="card-body p-4 bg-gradient bg-primary bg-opacity-10 border-start border-4 border-primary">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-3 shadow-sm" style="width: 56px; height: 56px;">
                            {{ mb_substr($selectedPatient->MR_PAT_FULL_NAME, 0, 1) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h2 class="fw-bolder fs-4 mb-0 text-dark">{{ $selectedPatient->MR_PAT_FULL_NAME }}</h2>
                            <span class="badge bg-primary text-white px-3 py-1 fs-7 rounded-pill">
                                <i class="bi bi-card-text me-1"></i>رقم الهوية: {{ $selectedPatient->MR_PAT_ID_NO }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-7">
                            <span><i class="bi bi-gender-ambiguous me-1 text-primary"></i>الجنس: <strong class="text-dark">{{ $selectedPatient->MR_PAT_GENDER === 'MALE' ? 'ذكر' : 'أنثى' }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-telephone me-1 text-primary"></i>الهاتف: <strong class="text-dark">{{ $selectedPatient->MR_PAT_PHONE ?: 'غير مسجل' }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-calendar-event me-1 text-primary"></i>تاريخ الميلاد: <strong class="text-dark">{{ $selectedPatient->MR_PAT_DOB ? \Carbon\Carbon::parse($selectedPatient->MR_PAT_DOB)->format('Y-m-d') : 'غير محدد' }}</strong></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto text-md-end">
                        <div class="d-flex flex-row flex-md-column gap-2">
                            <div class="bg-white rounded-3 p-2 px-3 border shadow-2xs text-center">
                                <span class="d-block text-muted fs-8">إجمالي التسجيلات</span>
                                <span class="fw-bolder fs-5 text-primary">{{ $selectedPatient->registrations->count() }} تسجيل</span>
                            </div>
                            <div class="bg-white rounded-3 p-2 px-3 border shadow-2xs text-center">
                                <span class="d-block text-muted fs-8">إجمالي الزيارات والمراجعات</span>
                                <span class="fw-bolder fs-5 text-success">
                                    {{ $selectedPatient->registrations->sum(fn($r) => $r->dailyReviews->count() + $r->scheduledReviews->count()) }} زيارة
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REGISTRATIONS & REVIEWS GROUPED ACCORDION / STACK -->
        <h3 class="fw-bold fs-5 text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-layers-half text-primary"></i>سجلات التسجيلات السريرية والمراجعات الملحقة
        </h3>

        @forelse($selectedPatient->registrations as $regIndex => $reg)
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
                <!-- Registration Header -->
                <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom border-2 border-primary-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white px-3 py-1.5 fs-7 fw-bold rounded-pill">
                            تسجيل #{{ $selectedPatient->registrations->count() - $regIndex }}
                        </span>
                        <code class="text-primary fw-bold fs-7">{{ $reg->WC_REG_CODE }}</code>
                        <span class="text-muted fs-7 me-2">
                            <i class="bi bi-hospital me-1 text-primary"></i>القسم المحول منه: <strong>{{ $reg->department?->WC_DEPT_NAME ?? 'عيادة الجروح' }}</strong>
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($reg->WC_REG_STATUS === 'ACTIVE')
                            <span class="badge bg-success text-white border border-success-subtle px-3 py-1.5 fs-7 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i>تسجيل نشط (ACTIVE)
                            </span>
                        @else
                            <span class="badge bg-secondary text-white border border-secondary-subtle px-3 py-1.5 fs-7 rounded-pill">
                                <i class="bi bi-box-arrow-right me-1"></i>مُخرج (DISCHARGED)
                            </span>
                        @endif

                        <span class="text-muted fs-7">
                            <i class="bi bi-calendar3 me-1 text-primary"></i>تاريخ التسجيل: <strong>{{ \Carbon\Carbon::parse($reg->WC_REG_DATE)->format('Y-m-d H:i') }}</strong>
                        </span>

                        <a href="{{ route('registrations.show', $reg->WC_REG_CODE) }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-folder2-open me-1"></i>فتح ملف التسجيل
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 bg-light-subtle">
                    <!-- Registration Info Grid -->
                    <div class="row g-3 mb-4 p-3 bg-white rounded-3 border">
                        <div class="col-12 col-md-4">
                            <span class="d-block text-muted fs-8 mb-1"><i class="bi bi-person-badge text-primary me-1"></i>الممرض المسجل:</span>
                            <span class="fw-bold text-dark fs-7">{{ $reg->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</span>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="d-block text-muted fs-8 mb-1"><i class="bi bi-calendar-check text-primary me-1"></i>حالة التخريج:</span>
                            @if($reg->WC_REG_STATUS === 'DISCHARGED')
                                <span class="fw-bold text-danger fs-7">
                                    تاريخ التخريج: {{ \Carbon\Carbon::parse($reg->WC_REG_DISCHARGED_AT)->format('Y-m-d H:i') }}
                                    ({{ $reg->dischargingNurse?->PER_EMP_FULL_NAME ?? 'ممرض العيادة' }})
                                </span>
                            @else
                                <span class="fw-bold text-success fs-7">مستمر بالعلاج بالمتابعة السريرية</span>
                            @endif
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="d-block text-muted fs-8 mb-1"><i class="bi bi-bandaid text-primary me-1"></i>التشخيصات والجروح المسجلة:</span>
                            <span class="fw-bold text-dark fs-7">{{ $reg->diagnoses->count() }} تشخيصات</span>
                        </div>
                    </div>

                    <!-- Diagnoses Wounds Summary -->
                    @if($reg->diagnoses->isNotEmpty())
                        <div class="mb-4">
                            <h6 class="fw-bold fs-7 text-dark mb-2">
                                <i class="bi bi-bandaid text-primary me-1"></i>تشخيصات الجروح في هذا التسجيل:
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($reg->diagnoses as $diag)
                                    <span class="badge bg-white text-dark border p-2.5 fs-7 rounded-3 shadow-2xs d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-patch-minus text-primary"></i>
                                        <strong class="text-primary">{{ $diag->woundType?->WC_WTYPE_NAME }}</strong> - {{ $diag->diagnosis?->WC_DIAG_NAME }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- REVIEWS SECTION UNDER THIS REGISTRATION -->
                    <div class="border-top pt-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold fs-6 text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary"></i>المراجعات والزيارات التابعة لهذا التسجيل
                            </h5>
                            <div class="d-flex align-items-center gap-2 fs-8">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-sun-fill me-1"></i>مراجعة يومية
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-calendar-check-fill me-1"></i>مراجعة مجدولة
                                </span>
                            </div>
                        </div>

                        <!-- Combined Reviews Timeline -->
                        @php
                            $dailyRevs = $reg->dailyReviews;
                            $scheduledRevs = $reg->scheduledReviews;
                        @endphp

                        @if($dailyRevs->isEmpty() && $scheduledRevs->isEmpty())
                            <div class="text-center py-4 bg-white rounded-3 border">
                                <i class="bi bi-clipboard2-x fs-2 text-muted d-block mb-2"></i>
                                <p class="fw-bold fs-7 text-dark mb-0">لا توجد زيارات يومية أو مواعيد مجدولة مسجلة ضمن هذا التسجيل.</p>
                            </div>
                        @else
                            <div class="vstack gap-3">
                                <!-- DAILY REVIEWS -->
                                @foreach($dailyRevs as $revIndex => $review)
                                    <div class="card border-0 shadow-2xs rounded-3 bg-white">
                                        <div class="card-header bg-white py-2.5 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary text-white fs-8 rounded-pill px-2.5 py-1">
                                                    زيارات #{{ $dailyRevs->count() - $revIndex }}
                                                </span>

                                                <!-- DISTINGUISH DAILY VS SCHEDULED REVIEW BADGE -->
                                                @if($review->WC_DREV_SCHEDULED_REVIEW_CD || $review->scheduledReview)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fs-8 rounded-pill fw-bold">
                                                        <i class="bi bi-calendar-check-fill me-1"></i>مراجعة مجدولة (تم التنفيذ)
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-8 rounded-pill fw-bold">
                                                        <i class="bi bi-sun-fill me-1"></i>مراجعة يومية مباشرة
                                                    </span>
                                                @endif

                                                <span class="fw-bold text-dark fs-7">
                                                    <i class="bi bi-calendar3 me-1 text-primary"></i>{{ \Carbon\Carbon::parse($review->WC_DREV_VISIT_DATE)->format('Y-m-d H:i') }}
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <!-- Status Badge -->
                                                @if($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_SCHEDULED')
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-calendar-check-fill me-1"></i>تحسن وحجز مراجعة جديدة
                                                    </span>
                                                @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_DISCHARGE')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-check-circle-fill me-1"></i>تحسن بدون مراجعة جديدة
                                                    </span>
                                                @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_SCHEDULED')
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>تدهور مع مراجعة جديدة
                                                    </span>
                                                @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_ADMISSION')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-hospital-fill me-1"></i>تدهور مع تسجيل دخول للمستشفى
                                                    </span>
                                                @elseif($review->WC_DREV_OVERALL_STATUS === 'IMPROVED_STAY')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-graph-up-arrow me-1"></i>تحسن مع استمرار
                                                    </span>
                                                @elseif($review->WC_DREV_OVERALL_STATUS === 'WORSENED_STAY')
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-arrow-repeat me-1"></i>تدهور (مستمر بالعلاج)
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border px-2.5 py-1 fs-8 rounded-pill">
                                                        {{ $review->WC_DREV_OVERALL_STATUS }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-8 mb-2 pb-2 border-bottom">
                                                <span>
                                                    <i class="bi bi-person-badge me-1 text-primary"></i>الممرض الفاحص: <strong class="text-dark">{{ $review->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
                                                </span>
                                                <span>&bull;</span>
                                                <span>
                                                    <i class="bi bi-pencil-square me-1 text-primary"></i>مُدخل البيانات: <strong class="text-dark">{{ $review->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</strong>
                                                </span>
                                            </div>

                                            @if($review->WC_DREV_GENERAL_NOTES)
                                                <div class="mb-2 p-2 px-3 bg-light rounded-2 border fs-7 text-dark">
                                                    <strong class="d-block fs-8 text-muted"><i class="bi bi-journal-text me-1 text-primary"></i>ملاحظات الزيارة:</strong>
                                                    {{ $review->WC_DREV_GENERAL_NOTES }}
                                                </div>
                                            @endif

                                            <!-- Wound Details Grid -->
                                            @if($review->details->isNotEmpty())
                                                <div class="row g-2 mt-1">
                                                    @foreach($review->details as $detail)
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-2 px-3 border rounded-2 bg-light-subtle fs-7">
                                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                                    <strong class="text-dark fs-8">
                                                                        {{ $detail->regDiagnosis?->woundType?->WC_WTYPE_NAME }} - {{ $detail->regDiagnosis?->diagnosis?->WC_DIAG_NAME }}
                                                                    </strong>
                                                                    <span class="badge bg-light text-dark border fs-8">
                                                                        {{ $detail->WC_DREVD_OUTCOME_STATUS }}
                                                                    </span>
                                                                </div>
                                                                <div class="text-muted fs-8">
                                                                    <span>الضمادة: <strong class="text-dark">{{ $detail->dressing?->WC_DRESS_NAME ?? 'لا توجد' }}</strong></span> |
                                                                    <span>العلاج: <strong class="text-dark">{{ $detail->medication?->WC_MED_NAME ?? 'لا يوجد' }}</strong></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <!-- SCHEDULED REVIEWS (UPCOMING / APPOINTMENTS) -->
                                @foreach($scheduledRevs as $srev)
                                    <div class="card border-0 shadow-2xs rounded-3 bg-white">
                                        <div class="card-header bg-white py-2.5 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <!-- DISTINCT SCHEDULED REVIEW BADGE -->
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fs-8 rounded-pill fw-bold">
                                                    <i class="bi bi-calendar-event-fill me-1"></i>مراجعة مجدولة (موعد)
                                                </span>

                                                <span class="fw-bold text-dark fs-7">
                                                    <i class="bi bi-calendar2-week me-1 text-info"></i>تاريخ الموعد: <strong>{{ \Carbon\Carbon::parse($srev->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') }}</strong>
                                                </span>
                                            </div>

                                            <div>
                                                @if($srev->WC_SREV_STATUS === 'COMPLETED')
                                                    <span class="badge bg-success text-white px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-check-circle me-1"></i>مكتملة
                                                    </span>
                                                @elseif($srev->WC_SREV_STATUS === 'MISSED')
                                                    <span class="badge bg-danger text-white px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-x-circle me-1"></i>لم يحضر
                                                    </span>
                                                @elseif($srev->WC_SREV_STATUS === 'CANCELLED')
                                                    <span class="badge bg-secondary text-white px-2.5 py-1 fs-8 rounded-pill">
                                                        ملغاة
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark px-2.5 py-1 fs-8 rounded-pill">
                                                        <i class="bi bi-clock me-1"></i>قيد الانتظار
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body p-3 fs-7 text-muted">
                                            <div class="d-flex align-items-center gap-3">
                                                <span><i class="bi bi-hash me-1 text-info"></i>تسلسل الزيارة: <strong>#{{ $srev->WC_SREV_VISIT_SERIAL }}</strong></span>
                                                @if($srev->WC_SREV_NOTES)
                                                    <span>&bull;</span>
                                                    <span>ملاحظات الموعد: {{ $srev->WC_SREV_NOTES }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-5 text-center bg-white">
                <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-dark mb-1">لا توجد تسجيلات سريرية لهذا المريض</h5>
                <p class="text-muted fs-7 mb-0">المريض موجود بالنظام ولكن لم يتم إنشاء أي جلسة تسجيل بالعيادة له بعد.</p>
            </div>
        @endforelse
    @else
        <!-- INITIAL EMPTY SEARCH STATE -->
        <div class="card border-0 shadow-sm p-5 text-center bg-white">
            <div class="py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-search fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">استعلام السجل الطبي الشامل للمريض</h4>
                <p class="text-muted fs-6 mb-0 mx-auto" style="max-width: 500px;">
                    يرجى إدخال رقم الهوية الوطنية، رقم الملف، أو اسم المريض في مربع البحث أعلاه للاستعلام عن كامل التسجيلات والمراجعات اليومية والمجدولة.
                </p>
            </div>
        </div>
    @endif

</div>
@endsection
