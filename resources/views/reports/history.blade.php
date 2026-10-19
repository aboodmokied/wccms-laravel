@extends('layouts.app')

@section('title', 'سجل المريض والتقارير التجميعية')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>سجل المريض والتقارير التجميعية</h4>
        <p class="text-muted small mb-0">البحث بالسجل السريري الشامل للمريض وتتبع التئام الجروح والزيارات</p>
    </div>
</div>

<x-card title="البحث في السجلات السريرية" icon="bi-search">
    <form method="GET" action="{{ route('reports.history') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted"><i class="bi bi-person-badge"></i></span>
                <input type="text" name="search" class="form-control" placeholder="ابحث باسم المريض، رقم الملف، أو رقم الهوية الوطنية..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>بحث</button>
            @if($search)
                <a href="{{ route('reports.history') }}" class="btn btn-outline-secondary" title="إعادة ضبط"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
        </div>
    </form>
</x-card>

@if($search && ! $history)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 my-4">
        <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
        <div>
            <h6 class="fw-bold mb-1">لم يتم العثور على سجلات لهذا المريض</h6>
            <span class="small text-muted">تأكد من كتابة رقم الهوية أو الاسم بشكل صحيح وأعد المحاولة.</span>
        </div>
    </div>
@elseif($history)
    <!-- Patient Profile Card -->
    <div class="card border-0 shadow-sm mb-4 mt-4">
        <div class="card-body bg-light rounded-3 p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                        {{ mb_substr($history['patient']['name'], 0, 1) }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">{{ $history['patient']['name'] }}</h5>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                            <span><i class="bi bi-card-text me-1"></i>رمز المريض: <strong>{{ $history['patient']['code'] }}</strong></span>
                            <span><i class="bi bi-person-vcard me-1"></i>رقم الهوية: <strong>{{ $history['patient']['civil_id'] }}</strong></span>
                            <span><i class="bi bi-gender-ambiguous me-1"></i>الجنس: <strong>{{ $history['patient']['gender'] }}</strong></span>
                            <span><i class="bi bi-telephone me-1"></i>الهاتف: <strong>{{ $history['patient']['phone'] }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- CSV Export Button (FR-009) -->
                <div>
                    <a href="{{ route('reports.history.export', ['patient' => $history['patient']['code']]) }}" class="btn btn-success shadow-sm">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>تصدير ملف CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Chronological Clinical Registrations & Timeline -->
    @if(empty($history['registrations']))
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
            لا توجد جلسات تسجيل سريرية لهذه الحالة حتى الآن.
        </div>
    @else
        <div class="accordion mb-4" id="registrationsAccordion">
            @foreach($history['registrations'] as $index => $reg)
                <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="heading-{{ $reg['code'] }}">
                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-white py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $reg['code'] }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 pe-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $reg['status'] === 'REGISTERED' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $reg['status_label'] }}
                                    </span>
                                    <span class="fw-bold text-dark">جلسة تسجيل #{{ $reg['code'] }}</span>
                                    <span class="text-muted small">({{ $reg['department'] }})</span>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar-event me-1"></i>تاريخ التسجيل: {{ $reg['registration_date'] }}
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $reg['code'] }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#registrationsAccordion">
                        <div class="accordion-body bg-white border-top">
                            <!-- Registration Details -->
                            <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                                <div class="col-12 col-md-4">
                                    <small class="text-muted d-block">الممرض المسؤول:</small>
                                    <span class="fw-semibold">{{ $reg['nurse'] }}</span>
                                </div>
                                <div class="col-12 col-md-4">
                                    <small class="text-muted d-block">تاريخ الخروج:</small>
                                    <span class="fw-semibold">{{ $reg['discharge_date'] ?? 'مستمر بالعلاج' }}</span>
                                </div>
                                <div class="col-12 col-md-4">
                                    <small class="text-muted d-block">عدد التقييمات السريرية:</small>
                                    <span class="fw-semibold">{{ count($reg['daily_reviews']) }} تقييمًا</span>
                                </div>
                            </div>

                            <!-- Daily Reviews Timeline -->
                            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-activity text-primary me-2"></i>زيارات التقييم السريري اليومية</h6>
                            @if(empty($reg['daily_reviews']))
                                <p class="text-muted small mb-4">لا توجد زيارات تقييم يومية سجلت في هذه الجلسة.</p>
                            @else
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered align-middle small mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>تاريخ التقييم</th>
                                                <th><bdi>{{ __('clinical.fields.overall_outcome') }}</bdi></th>
                                                <th>الممرض السريري</th>
                                                <th>الملاحظات والتغيرات السريرية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reg['daily_reviews'] as $rev)
                                                <tr>
                                                    <td>{{ $rev['date'] }}</td>
                                                    <td>
                                                        @php($outcome = (string) $rev['status_eval'])
                                                        <span class="badge {{ $outcome === 'DEATH' ? 'bg-danger' : (str_starts_with($outcome, 'IMPROVED') ? 'bg-success' : 'bg-warning text-dark') }}">{{ $rev['status_label'] }}</span>
                                                    </td>
                                                    <td>{{ $rev['nurse'] }}</td>
                                                    <td>{{ $rev['notes'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Scheduled Reviews List -->
                            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-calendar-check text-info me-2"></i>المواعيد المجدولة والمتابعة</h6>
                            @if(empty($reg['scheduled_reviews']))
                                <p class="text-muted small">لا توجد مواعيد مجدولة مرتبطة بهذه الجلسة.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle small mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>تاريخ الموعد المجدول</th>
                                                <th>الحالة</th>
                                                <th>المسؤول</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reg['scheduled_reviews'] as $srev)
                                                <tr>
                                                    <td>{{ $srev['scheduled_date'] }}</td>
                                                    <td>
                                                        @if($srev['status'] === 'COMPLETED')
                                                            <span class="badge bg-success">مكتمل</span>
                                                        @elseif($srev['status'] === 'MISSED')
                                                            <span class="badge bg-danger">متخلف عن الموعد</span>
                                                        @elseif($srev['status'] === 'CANCELLED')
                                                            <span class="badge bg-secondary">ملغى</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $srev['nurse'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif
@endsection
