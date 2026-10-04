@extends('layouts.app')

@section('title', 'الرئيسية - لوحة التحكم التنفيذية')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>لوحة التحليلات والمؤشرات التنفيذية</h4>
        <p class="text-muted small mb-0">عرض وتحليل الأداء السريري المباشر ونسب التعافي والتزام المواعيد</p>
    </div>
    <!-- Quick Period Filter Buttons (US3) -->
    <div class="btn-group shadow-sm" role="group" aria-label="تصفية الفترة الزمنية">
        <a href="{{ route('dashboard', ['period' => 'all']) }}" 
           class="btn btn-sm {{ ($kpis['period'] ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
           كافة الأوقات
        </a>
        <a href="{{ route('dashboard', ['period' => '30days']) }}" 
           class="btn btn-sm {{ ($kpis['period'] ?? '') === '30days' ? 'btn-primary' : 'btn-outline-primary' }}">
           آخر 30 يومًا
        </a>
        <a href="{{ route('dashboard', ['period' => 'ytd']) }}" 
           class="btn btn-sm {{ ($kpis['period'] ?? '') === 'ytd' ? 'btn-primary' : 'btn-outline-primary' }}">
           السنة الحالية
        </a>
    </div>
</div>

<!-- 4 Primary KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">المرضى المسجلون حالياً</h6>
                    <h2 class="fw-bold mb-0 text-white">{{ number_format($kpis['active_patients'] ?? 0) }}</h2>
                    <span class="badge bg-white text-primary mt-2">حالات نشطة بالعيادة</span>
                </div>
                <i class="bi bi-people-fill fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">متوسط مدة العلاج</h6>
                    <h2 class="fw-bold mb-0 text-white">{{ number_format($kpis['avg_treatment_days'] ?? 0) }} <span class="fs-6 font-normal">يوم</span></h2>
                    <span class="badge bg-white text-success mt-2">من التسجيل حتى الخروج</span>
                </div>
                <i class="bi bi-clock-history fs-1 opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-dark-50 small mb-1">نسبة التخلف عن المواعيد</h6>
                    <h2 class="fw-bold mb-0 text-dark">{{ $kpis['missed_appointment_rate'] ?? 0 }}%</h2>
                    <span class="badge bg-dark text-warning mt-2">مواعيد تفويتها (Missed)</span>
                </div>
                <i class="bi bi-calendar-x-fill fs-1 opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 small mb-1">طلبات التسجيل المعلقة</h6>
                    <h2 class="fw-bold mb-0 text-white">{{ number_format($kpis['pending_requests'] ?? 0) }}</h2>
                    <span class="badge bg-white text-info mt-2">بانتظار الفرز والقبول</span>
                </div>
                <i class="bi bi-inbox-fill fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Clinical Outcome Breakdown Progress Bars -->
    <div class="col-12 col-lg-5">
        <x-card title="توزيع نتائج التقييمات السريرية" icon="bi-pie-chart-fill" subtitle="تحليل حالات المرضى والنتائج التقييمية">
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i>تحسن وتم الخروج (Discharged)</span>
                    <span class="text-muted">{{ $kpis['outcome_distribution']['DISCHARGED']['count'] ?? 0 }} حالات ({{ $kpis['outcome_distribution']['DISCHARGED']['percentage'] ?? 0 }}%)</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $kpis['outcome_distribution']['DISCHARGED']['percentage'] ?? 0 }}%"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-primary"><i class="bi bi-arrow-repeat me-1"></i>تحسن يحتاج متابعة (Follow-up)</span>
                    <span class="text-muted">{{ $kpis['outcome_distribution']['FOLLOWUP']['count'] ?? 0 }} حالات ({{ $kpis['outcome_distribution']['FOLLOWUP']['percentage'] ?? 0 }}%)</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $kpis['outcome_distribution']['FOLLOWUP']['percentage'] ?? 0 }}%"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>تدهور في الحالة (Worsened)</span>
                    <span class="text-muted">{{ $kpis['outcome_distribution']['WORSENED']['count'] ?? 0 }} حالات ({{ $kpis['outcome_distribution']['WORSENED']['percentage'] ?? 0 }}%)</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $kpis['outcome_distribution']['WORSENED']['percentage'] ?? 0 }}%"></div>
                </div>
            </div>

            <div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-danger"><i class="bi bi-x-circle-fill me-1"></i>حالات وفاة (Deceased)</span>
                    <span class="text-muted">{{ $kpis['outcome_distribution']['DECEASED']['count'] ?? 0 }} حالات ({{ $kpis['outcome_distribution']['DECEASED']['percentage'] ?? 0 }}%)</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $kpis['outcome_distribution']['DECEASED']['percentage'] ?? 0 }}%"></div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Clinical Activities Table -->
    <div class="col-12 col-lg-7">
        <x-card title="أحدث التقييمات والزيارات السريرية" icon="bi-activity">
            @if(empty($recentActivities))
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                    لا توجد تقييمات سريرية مسجلة حديثاً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم المريض</th>
                                <th>التاريخ</th>
                                <th>الممرض المسؤول</th>
                                <th>نتيجة التقييم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $act)
                                <tr>
                                    <td>
                                        <a href="{{ route('reports.history', ['search' => $act['patient_code']]) }}" class="fw-semibold text-decoration-none">
                                            {{ $act['patient_name'] }}
                                        </a>
                                        <div class="small text-muted">{{ $act['patient_code'] }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ \Carbon\Carbon::parse($act['date'])->format('Y-m-d') }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($act['date'])->format('H:i') }}</div>
                                    </td>
                                    <td class="small">{{ $act['nurse_name'] }}</td>
                                    <td>
                                        @if($act['status'] === 'DISCHARGED')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ $act['status_label'] }}</span>
                                        @elseif($act['status'] === 'FOLLOWUP')
                                            <span class="badge bg-primary"><i class="bi bi-arrow-repeat me-1"></i>{{ $act['status_label'] }}</span>
                                        @elseif($act['status'] === 'WORSENED')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>{{ $act['status_label'] }}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>{{ $act['status_label'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
