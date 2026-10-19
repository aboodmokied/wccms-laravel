{{--
    Report filters.
    $mode: 'month' (one month) or 'range' (from/to); $exportQuery: query string for the Excel export.
--}}
@use('App\Services\Reports\ReportFormat')
@php($selectedCount = count($selectedDepartments))

<x-card title="خيارات التقرير" icon="bi-funnel">
    @if($errors->any())
        <div class="alert alert-danger border-0 py-2 small" role="alert">
            @foreach($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route(request()->route()->getName()) }}" class="row g-3 align-items-end">
        @if($mode === 'month')
            <div class="col-12 col-sm-6 col-lg-3">
                <label for="report-month" class="form-label small fw-semibold">الشهر</label>
                <input type="month" id="report-month" name="month" class="form-control"
                       value="{{ ReportFormat::monthInput($month) }}">
            </div>
        @else
            <div class="col-6 col-lg-3">
                <label for="report-from" class="form-label small fw-semibold">من شهر</label>
                <input type="month" id="report-from" name="from" class="form-control"
                       value="{{ ReportFormat::monthInput($from) }}">
            </div>
            <div class="col-6 col-lg-3">
                <label for="report-to" class="form-label small fw-semibold">إلى شهر</label>
                <input type="month" id="report-to" name="to" class="form-control"
                       value="{{ ReportFormat::monthInput($to) }}">
            </div>
        @endif

        <div class="col-12 col-sm-6 col-lg-3">
            <span class="form-label small fw-semibold d-block">الأقسام</span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <span>{{ $selectedCount === 0 ? 'كل الأقسام' : 'أقسام محددة: '.$selectedCount }}</span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <div class="dropdown-menu p-2 m8-report-departments">
                    @forelse($departments as $department)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="departments[]"
                                   id="department-{{ $department->WC_DEPT_CODE }}" value="{{ $department->WC_DEPT_CODE }}"
                                   @checked(in_array($department->WC_DEPT_CODE, $selectedDepartments, true))>
                            <label class="form-check-label small" for="department-{{ $department->WC_DEPT_CODE }}">
                                {{ $department->WC_DEPT_NAME }}
                            </label>
                        </div>
                    @empty
                        <span class="small text-muted">لا توجد أقسام.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>عرض</button>
            @isset($exportQuery)
                <a href="{{ route('reports.monthly.export', $exportQuery) }}" class="btn btn-success flex-grow-1">
                    <i class="bi bi-file-earmark-excel me-1"></i>تصدير Excel
                </a>
            @endisset
        </div>
    </form>
</x-card>
