@extends('layouts.app')

@section('title', 'تمت المراجعة اليوم - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE HEADER BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="fw-bolder fs-3 mb-1 text-dark">تمت المراجعة اليوم</h1>
            <p class="text-muted fs-7 mb-0">عرض جميع المرضى المسجلين الذين تمت مراجعتهم بنجاح خلال تاريخ اليوم</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('reviews.daily.pending') }}" class="btn btn-outline-warning btn-sm px-3 py-2 fs-7 rounded-pill text-dark">
                <i class="bi bi-hourglass-split me-1"></i> عرض المرضى المنتظرين اليوم
            </a>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7 rounded-pill fw-bold">
                <i class="bi bi-calendar-event me-1"></i>تاريخ اليوم: {{ now()->format('Y-m-d') }}
            </span>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('reviews.daily.completed') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control bg-light border-0 fs-7" placeholder="البحث باسم المريض أو رقم الهوية الوطنية...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2 rounded-2 fw-bold">بحث</button>
                    @if($search)
                        <a href="{{ route('reviews.daily.completed') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-2">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($registrations->count() > 0)
        <div class="row g-3">
            @foreach($registrations as $reg)
                @include('reviews.partials.daily_patient_card', ['reg' => $reg, 'variant' => 'completed'])
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center p-4 mb-3">
                <i class="bi bi-journal-x fs-1"></i>
            </div>
            <h4 class="fw-bolder fs-5 text-dark mb-1">لم تتم أي زيارات اليوم بعد</h4>
            <p class="text-muted fs-7 mb-0">لم يتم توثيق أي زيارات يومية اليوم حتى الآن.</p>
        </div>
    @endif

</div>

<style>
    .patient-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .patient-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
</style>
@endsection
