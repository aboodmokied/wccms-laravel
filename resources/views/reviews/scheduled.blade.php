@extends('layouts.app')

@section('title', 'المواعيد')

@push('styles')
    @vite(['resources/css/pages/reviews.css'])
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="wccms-page-heading mb-1">المواعيد</h1>
            <p class="text-muted mb-0">تابع مواعيد اليوم وابدأ الزيارة، أو اختر فترة أخرى.</p>
        </div>
    </div>

    {{-- Session Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @php
        $currentTab = strtoupper(request('tab', 'TODAY'));
    @endphp

    <nav class="wccms-appointment-tabs mb-4" aria-label="حالة الموعد">
        @foreach([
            'TODAY' => ['مواعيد اليوم', 'today_count'],
            'UPCOMING' => ['القادمة', 'upcoming_count'],
            'OVERDUE' => ['المتأخرة', 'overdue_count'],
            'COMPLETED' => ['المكتملة', 'completed_count'],
            'CANCELLED' => ['الملغاة', 'cancelled_count'],
        ] as $tab => [$label, $countKey])
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => $tab])) }}" @if($currentTab === $tab) aria-current="page" @endif>
                <span>{{ $label }}</span>
                <span class="badge bg-light text-dark">{{ $counts[$countKey] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Main Content Card --}}
    <div class="card border-0 shadow-sm">
        {{-- Card Body with Filter & Table --}}
        <div class="card-body p-4">

            {{-- Filter Form --}}
            <form action="{{ route('reviews.scheduled') }}" method="GET" class="mb-4">
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label for="appointment-search" class="form-label">البحث عن مريض</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="search" id="appointment-search" name="search" class="form-control border-start-0" placeholder="اسم المريض، الهوية، أو السجل..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>بحث
                        </button>
                        <a href="{{ route('reviews.scheduled', ['tab' => $currentTab]) }}" class="btn btn-outline-secondary" title="إعادة ضبط" aria-label="إعادة ضبط البحث والفلاتر">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
                <details class="wccms-disclosure mt-3" @if(request('date_from') || request('date_to')) open @endif>
                    <summary>تصفية حسب التاريخ</summary>
                    <div class="wccms-disclosure-content row g-3">
                        <div class="col-12 col-sm-6">
                            <label for="appointment-date-from" class="form-label">من تاريخ</label>
                            <input type="date" id="appointment-date-from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="appointment-date-to" class="form-label">إلى تاريخ</label>
                            <input type="date" id="appointment-date-to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">تطبيق الفلاتر</button>
                        </div>
                    </div>
                </details>
            </form>

            {{-- Table Queue --}}
            @if($scheduledReviews->count() > 0)
                <div class="table-responsive" tabindex="0" role="region" aria-label="قائمة المواعيد">
                    <table class="table table-hover align-middle border-top">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">المريض</th>
                                <th scope="col">القسم</th>
                                <th scope="col">الموعد</th>
                                <th scope="col">الحالة</th>
                                <th scope="col">ملاحظات</th>
                                <th scope="col" class="text-end">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduledReviews as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $item->registration?->patient?->full_name ?? 'غير محدد' }}
                                        </div>
                                        <div class="text-muted small">
                                            رقم الهوية: {{ $item->registration?->patient?->MRP_ID ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $item->registration?->department?->WC_DEPT_NAME ?? 'العيادة' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ \Illuminate\Support\Carbon::parse($item->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Carbon::parse($item->WC_SREV_SCHEDULED_DATE)->format('h:i A') }}
                                        </div>
                                        <div class="small text-muted">
                                            الزيارة #{{ $item->WC_SREV_VISIT_SERIAL ?? 1 }} · <bdi>{{ Str::limit($item->WC_SREV_CODE, 8, '') }}</bdi>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->WC_SREV_STATUS === 'PENDING')
                                            @if(\Illuminate\Support\Carbon::parse($item->WC_SREV_SCHEDULED_DATE)->isToday())
                                                <span class="badge bg-primary">اليوم</span>
                                            @elseif(\Illuminate\Support\Carbon::parse($item->WC_SREV_SCHEDULED_DATE)->isPast())
                                                <span class="badge bg-warning text-dark">متأخر</span>
                                            @else
                                                <span class="badge bg-info text-dark">قادم</span>
                                            @endif
                                        @elseif($item->WC_SREV_STATUS === 'COMPLETED')
                                            <span class="badge bg-success">تمت الزيارة</span>
                                        @elseif($item->WC_SREV_STATUS === 'CANCELLED')
                                            <span class="badge bg-secondary">ملغي</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $item->WC_SREV_STATUS }}</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 200px;">
                                        <small class="text-muted text-truncate d-block" title="{{ $item->WC_SREV_NOTES }}">
                                            {{ $item->WC_SREV_NOTES ?? '-' }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        @if($item->WC_SREV_STATUS === 'PENDING')
                                            <div class="d-flex flex-column align-items-end gap-1">
                                                {{-- Start Visit Form --}}
                                                <a href="{{ route('reviews.scheduled.create', $item) }}" class="btn btn-sm btn-primary" title="فتح نموذج المراجعة المجدولة">
                                                    <i class="bi bi-play-circle me-1"></i>إدخال المراجعة
                                                </a>

                                                <details class="wccms-row-actions">
                                                    <summary>خيارات الموعد</summary>
                                                    <div class="d-flex flex-column gap-2 py-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rescheduleModal-{{ $item->WC_SREV_CODE }}">
                                                            <i class="bi bi-calendar-event" aria-hidden="true"></i>إعادة جدولة
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $item->WC_SREV_CODE }}">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i>إلغاء الموعد
                                                        </button>
                                                    </div>
                                                </details>
                                            </div>

                                            @include('reviews.partials.reschedule-modal', ['item' => $item])
                                            @include('reviews.partials.cancel-modal', ['item' => $item])
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        عرض {{ $scheduledReviews->firstItem() ?? 0 }} إلى {{ $scheduledReviews->lastItem() ?? 0 }} من أصل {{ $scheduledReviews->total() }} موعد
                    </div>
                    <div>
                        {{ $scheduledReviews->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted display-4 d-block mb-3"></i>
                    <h5 class="text-secondary fw-bold">لا يوجد مواعيد مجدولة</h5>
                    <p class="text-muted small">لم يتم العثور على أي مواعيد تطابق الفلاتر المحددة.</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
