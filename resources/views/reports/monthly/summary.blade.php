@extends('layouts.app')

@section('title', 'ملخص الحالات')

@push('styles')
    @vite(['resources/css/pages/reports.css'])
@endpush

@use('App\Services\Reports\ReportFormat')

@php
    $perMonth = array_map(fn (array $row): array => [
        'label' => ReportFormat::month($row['month']),
        'count' => $row['count'],
    ], $summary['perMonth']);

    $genderTotal = max(1, array_sum(array_column($summary['perGender'], 'count')));
    $circumference = 2 * M_PI * 16;
    $offset = 0.0;
@endphp

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i>التقارير الشهرية</h4>
        <p class="text-muted small mb-0">
            ملخص الحالات من {{ ReportFormat::month($from) }} إلى {{ ReportFormat::month($to) }}
        </p>
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

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <x-card title="عدد الحالات حسب الشهر" icon="bi-bar-chart-line" :subtitle="'المجموع: '.$summary['totalCases']">
            @include('reports.monthly.partials.bars', ['items' => $perMonth])
        </x-card>
    </div>

    <div class="col-12 col-xl-6">
        <x-card title="الحالات حسب Gender" icon="bi-pie-chart" :subtitle="'المجموع: '.$summary['totalCases']">
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
                <svg class="m8-report-pie" viewBox="0 0 64 64" role="img" aria-label="الحالات حسب Gender">
                    <circle class="m8-report-pie-track" cx="32" cy="32" r="16"></circle>
                    @foreach($summary['perGender'] as $index => $gender)
                        @php($length = $gender['count'] / $genderTotal * $circumference)
                        @if($length > 0)
                            <circle class="m8-report-pie-slice-{{ $index }}" cx="32" cy="32" r="16"
                                    stroke-dasharray="{{ round($length, 3) }} {{ round($circumference, 3) }}"
                                    stroke-dashoffset="{{ round(-$offset, 3) }}"
                                    transform="rotate(-90 32 32)"></circle>
                        @endif
                        @php($offset += $length)
                    @endforeach
                </svg>
                <ul class="list-unstyled mb-0">
                    @foreach($summary['perGender'] as $index => $gender)
                        <li class="mb-2">
                            <span class="m8-report-legend-swatch m8-report-pie-slice-{{ $index }}"></span>
                            <span class="fw-semibold">{{ $gender['label'] }}</span>:
                            <span class="fw-bold">{{ $gender['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-card>
    </div>

    <div class="col-12">
        <x-card title="الحالات حسب Diagnosis" icon="bi-clipboard2-pulse" subtitle="الحالة ذات أكثر من Diagnosis تُحسب تحت كل واحد منها">
            @if($summary['perDiagnosis'] === [])
                <div class="text-center text-muted py-4">لا توجد حالات في هذه الفترة.</div>
            @else
                @include('reports.monthly.partials.bars', ['items' => $summary['perDiagnosis']])
            @endif
        </x-card>
    </div>
</div>
@endsection
