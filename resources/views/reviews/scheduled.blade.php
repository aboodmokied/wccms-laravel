@extends('layouts.app')

@section('title', 'إدارة المواعيد والمراجعات المجدولة')

@push('styles')
    @vite(['resources/css/pages/reviews.css'])
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-calendar-check-fill text-primary me-2"></i>جدول المواعيد والمراجعات المجدولة
            </h3>
            <p class="text-muted small mb-0">متابعة مواعيد الزيارات القادمة والسابقة للتمريض وإدارة التقييمات المجدولة</p>
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

    {{-- Summary Ribbon Cards --}}
    <div class="row g-3 mb-4">
        @php
            $currentTab = strtoupper(request('tab', 'TODAY'));
        @endphp

        <div class="col-6 col-md">
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => 'TODAY'])) }}"
               class="card border-0 shadow-sm text-decoration-none h-100 {{ $currentTab === 'TODAY' ? 'border-start border-primary border-4 bg-primary-subtle' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">مواعيد اليوم</div>
                        <div class="fs-4 fw-bold text-primary">{{ $counts['today_count'] }}</div>
                    </div>
                    <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-calendar-day fs-5"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md">
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => 'UPCOMING'])) }}"
               class="card border-0 shadow-sm text-decoration-none h-100 {{ $currentTab === 'UPCOMING' ? 'border-start border-info border-4 bg-info-subtle' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">المواعيد القادمة</div>
                        <div class="fs-4 fw-bold text-info">{{ $counts['upcoming_count'] }}</div>
                    </div>
                    <div class="rounded-circle bg-info text-white p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-calendar-plus fs-5"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md">
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => 'OVERDUE'])) }}"
               class="card border-0 shadow-sm text-decoration-none h-100 {{ $currentTab === 'OVERDUE' ? 'border-start border-warning border-4 bg-warning-subtle' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">متأخرة عن الموعد</div>
                        <div class="fs-4 fw-bold text-warning">{{ $counts['overdue_count'] }}</div>
                    </div>
                    <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md">
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => 'COMPLETED'])) }}"
               class="card border-0 shadow-sm text-decoration-none h-100 {{ $currentTab === 'COMPLETED' ? 'border-start border-success border-4 bg-success-subtle' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">تم إنجازها</div>
                        <div class="fs-4 fw-bold text-success">{{ $counts['completed_count'] }}</div>
                    </div>
                    <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-check2-square fs-5"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md">
            <a href="{{ route('reviews.scheduled', array_merge(request()->except(['tab', 'page']), ['tab' => 'CANCELLED'])) }}"
               class="card border-0 shadow-sm text-decoration-none h-100 {{ $currentTab === 'CANCELLED' ? 'border-start border-secondary border-4 bg-secondary-subtle' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ملغاة</div>
                        <div class="fs-4 fw-bold text-secondary">{{ $counts['cancelled_count'] }}</div>
                    </div>
                    <div class="rounded-circle bg-secondary text-white p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-x-circle fs-5"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="card border-0 shadow-sm">
        {{-- Card Body with Filter & Table --}}
        <div class="card-body p-4">

            {{-- Filter Form --}}
            <form action="{{ route('reviews.scheduled') }}" method="GET" class="mb-4">
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">البحث عن مريض</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="اسم المريض، الهوية، أو السجل..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-bold">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-bold">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>تصفية
                        </button>
                        <a href="{{ route('reviews.scheduled', ['tab' => $currentTab]) }}" class="btn btn-outline-secondary" title="إعادة ضبط">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table Queue --}}
            @if($scheduledReviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead class="table-light">
                            <tr>
                                <th># الموعد</th>
                                <th>المريض</th>
                                <th>القسم المعالج</th>
                                <th>تاريخ الموعد المجدول</th>
                                <th>رقم الزيارة</th>
                                <th>الحالة</th>
                                <th>ملاحظات</th>
                                <th class="text-end">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduledReviews as $item)
                                <tr>
                                    <td class="fw-bold text-secondary">
                                        <small>{{ Str::limit($item->WC_SREV_CODE, 8, '') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $item->registration?->patient?->MR_PAT_FULL_NAME ?? 'غير محدد' }}
                                        </div>
                                        <div class="text-muted small">
                                            رقم الهوية: {{ $item->registration?->patient?->MR_PAT_NATIONAL_ID ?? $item->registration?->patient?->MR_PAT_ID_NO ?? '-' }}
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
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            الزيارة #{{ $item->WC_SREV_VISIT_SERIAL ?? 1 }}
                                        </span>
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
                                            <div class="d-flex justify-content-end gap-1">
                                                {{-- Start Visit Form --}}
                                                <form action="{{ route('reviews.scheduled.fulfill', $item->WC_SREV_CODE) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="بدء الزيارة والتقييم">
                                                        <i class="bi bi-play-circle me-1"></i>بدء الزيارة
                                                    </button>
                                                </form>

                                                {{-- Reschedule Trigger --}}
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rescheduleModal-{{ $item->WC_SREV_CODE }}" title="إعادة جدولة الموعد">
                                                    <i class="bi bi-calendar-event"></i>
                                                </button>

                                                {{-- Cancel Trigger --}}
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $item->WC_SREV_CODE }}" title="إلغاء الموعد">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
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
