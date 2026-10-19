@extends('layouts.app')

@section('title', 'التقارير الشهرية')

@push('styles')
    @vite(['resources/css/pages/reports.css'])
@endpush

@use('App\Services\Reports\ReportFormat')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i>التقارير الشهرية</h4>
        <p class="text-muted small mb-0">حالات العيادة المسجلة في الشهر المحدد، صف لكل حالة</p>
    </div>
</div>

@include('reports.monthly.partials.tabs')

@include('reports.monthly.partials.filters', [
    'mode' => 'month',
    'exportQuery' => [
        'from' => ReportFormat::monthInput($month),
        'to' => ReportFormat::monthInput($month),
        'departments' => $selectedDepartments,
    ],
])

<x-card :title="'حالات '.ReportFormat::month($month)" icon="bi-list-ul" :subtitle="'عدد الحالات: '.$cases->count()">
    @if($cases->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            لا توجد حالات في هذا الشهر.
        </div>
    @else
        @include('reports.monthly.partials.case-table', [
            'rows' => $cases->map(fn ($case) => ['case' => $case]),
            'withPrevious' => false,
        ])
    @endif
</x-card>
@endsection
