@extends('layouts.app')

@section('title', 'المرضى المسجلون - WCCMS')

@section('content')
<div class="container-fluid px-0">
    <!-- PAGE TITLE ROW -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="fw-bolder fs-2 mb-1">المرضى المسجلون في العيادة</h1>
            <p class="text-muted fs-6 mb-0">عرض السجلات النشطة والمخرجة في عيادة العناية بالجروح والمتابعة السريرية.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('requests.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-send-plus-fill"></i>
                <span>طلبات التسجيل الواردة</span>
            </a>
        </div>
    </div>

    <!-- FILTER TABS & SEARCH CARD -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <!-- Status Tabs -->
                <div class="col-12 col-md-6">
                    <div class="btn-group w-100 w-sm-auto" role="group">
                        <a href="{{ route('registrations.index', ['status' => 'ACTIVE', 'search' => $searchQuery]) }}"
                           class="btn {{ $currentStatus === 'ACTIVE' ? 'btn-success fw-bold' : 'btn-outline-secondary' }} px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> الحالات النشطة
                        </a>
                        <a href="{{ route('registrations.index', ['status' => 'DISCHARGED', 'search' => $searchQuery]) }}"
                           class="btn {{ $currentStatus === 'DISCHARGED' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }} px-4">
                            <i class="bi bi-box-arrow-right me-1"></i> الحالات المخرجة
                        </a>
                    </div>
                </div>

                <!-- Search Input Form -->
                <div class="col-12 col-md-6">
                    <form method="GET" action="{{ route('registrations.index') }}" class="d-flex gap-2">
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="البحث بالاسم أو رقم الهوية..." value="{{ $searchQuery }}">
                            <button type="submit" class="btn btn-primary px-4">بحث</button>
                            @if($searchQuery)
                                <a href="{{ route('registrations.index', ['status' => $currentStatus]) }}" class="btn btn-light" title="إلغاء البحث">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PATIENTS TABLE CARD -->
    <div class="card shadow-sm">
        <div class="card-header border-0 pt-4 pb-2 d-flex align-items-center justify-content-between">
            <h2 class="card-title fs-5 mb-0">
                <i class="bi bi-table me-2 text-primary"></i>قائمة المرضى ({{ $currentStatus === 'ACTIVE' ? 'الحالات النشطة' : 'الحالات المخرجة' }})
            </h2>
            <span class="badge badge-light-secondary fs-7">{{ $registrations->total() }} سجل</span>
        </div>

        <div class="table-responsive px-4">
            <table class="table align-middle table-hover">
                <thead>
                    <tr class="text-muted fw-bold fs-7 text-uppercase border-bottom">
                        <th>المريض (الهوية / الاسم)</th>
                        <th>القسم المحول منه</th>
                        <th>تاريخ التسجيل</th>
                        <th>الممرض المسجل</th>
                        <th>حالة التسجيل</th>
                        <th>مراجعة اليوم</th>
                        <th class="text-center">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark fs-6">{{ $reg->patient?->MR_PAT_FULL_NAME ?? 'غير محدد' }}</span>
                                    <span class="text-muted fs-7">رقم الهوية: {{ $reg->patient?->MR_PAT_ID_NO }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light-primary text-primary px-3 py-2">
                                    <i class="bi bi-hospital me-1"></i>{{ $reg->department?->WC_DEPT_NAME ?? 'العيادة' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-dark fs-7 fw-semibold">
                                    {{ \Carbon\Carbon::parse($reg->WC_REG_DATE)->format('Y-m-d H:i') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary fs-7">
                                    {{ $reg->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>
                                @if($reg->WC_REG_STATUS === 'ACTIVE')
                                    <span class="badge bg-success px-3 py-2 fs-7">نشط (Active)</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2 fs-7">مُخرج (Discharged)</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->is_reviewed_today)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fs-7 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i>تمت المراجعة
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 fs-7 rounded-pill">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>لم يُراجع اليوم
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('registrations.show', $reg->WC_REG_CODE) }}" class="btn btn-sm btn-light-primary px-3">
                                    <i class="bi bi-folder2-open me-1"></i> فتح الحالة
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="fw-bold fs-5 mb-1">لا توجد سجلات مرضى بهذه المعايير</p>
                                    <p class="fs-7">جرب تغيير حالة التصفية أو إلغاء البحث.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="card-footer py-3 d-flex justify-content-center border-0">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
