@extends('layouts.app')

@section('title', 'إعادة الدخول')

@push('styles')
    @vite(['resources/css/pages/reports.css'])
@endpush

@use('App\Services\Reports\ReportFormat')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i>التقارير الشهرية</h4>
        <p class="text-muted small mb-0">حالات المرضى الذين سبق لهم التسجيل في العيادة</p>
    </div>
</div>

@include('reports.monthly.partials.tabs')

@include('reports.monthly.partials.filters', [
    'mode' => 'range',
    'exportQuery' => [
        'from' => ReportFormat::monthInput($from),
        'to' => ReportFormat::monthInput($to),
        'departments' => $selectedDepartments,
    ],
])

<x-card :title="'إعادة الدخول من '.ReportFormat::month($from).' إلى '.ReportFormat::month($to)" icon="bi-arrow-repeat"
        :subtitle="'عدد الحالات: '.$readmissions->count()">
    @if($readmissions->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            لا توجد حالات إعادة دخول في هذه الفترة.
        </div>
    @else
        @include('reports.monthly.partials.case-table', [
            'rows' => $readmissions->map(fn ($readmission) => [
                'case' => $readmission->case,
                'previousRegisteredAt' => $readmission->previousRegisteredAt,
                'previousOutcome' => $readmission->previousOutcome,
            ]),
            'withPrevious' => true,
        ])
    @endif
</x-card>
@endsection
