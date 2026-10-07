@extends('layouts.app')

@section('title', 'البحث في السجلات الطبية - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="fw-bolder fs-3 mb-1 text-dark">
                <i class="bi bi-search text-primary me-2"></i>البحث عن مريض
            </h1>
            <p class="text-muted fs-7 mb-0">البحث برقم الهوية الوطنية، رقم الملف، أو الاسم لاستعراض السجل الطبي الشامل.</p>
        </div>
    </div>

    @include('patients.partials.patient_search_form')

    @if($search !== '' && $patients->isEmpty())
        <div class="card border-0 shadow-sm mb-4 bg-warning bg-opacity-10 border-warning">
            <div class="card-body p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-1 d-block mb-2"></i>
                <h5 class="fw-bold text-dark mb-1">لم يتم العثور على مريض مطابق لهذا البحث</h5>
                <p class="text-muted fs-7 mb-0">تأكد من كتابة الاسم أو رقم الهوية بالشكل الصحيح ثم أعد المحاولة.</p>
            </div>
        </div>
    @elseif($search !== '' && $patients->count() > 0)
        <!-- Search Results Grid -->
        <h4 class="fw-bold fs-5 text-dark mb-3">نتائج البحث ({{ $patients->count() }})</h4>
        <div class="row g-3">
            @foreach($patients as $patient)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white transition-all overflow-hidden border-top border-4 border-primary">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bolder fs-6 mb-1 text-dark">{{ $patient->MR_PAT_FULL_NAME }}</h5>
                                        <span class="fs-8 text-muted">هوية: {{ $patient->MR_PAT_ID_NO }}</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-light-subtle rounded-3 border mb-3">
                                    <div class="row g-2 fs-8 text-muted">
                                        <div class="col-6">
                                            <span><i class="bi bi-file-earmark-person me-1 text-primary"></i>رقم الملف:</span>
                                            <strong class="d-block text-dark">{{ $patient->MR_PAT_CODE }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span><i class="bi bi-calendar3 me-1 text-primary"></i>العمر:</span>
                                            <strong class="d-block text-dark">{{ \Carbon\Carbon::parse($patient->MR_PAT_DOB)->age }} سنة</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('patients.show', $patient->MR_PAT_CODE) }}" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-3 py-2 fw-bold shadow-2xs">
                                <i class="bi bi-folder2-open"></i>
                                <span>استعراض السجل الطبي</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- INITIAL EMPTY SEARCH STATE -->
        <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
            <div class="py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-person-vcard fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">النظام المركزي للسجلات الطبية</h4>
                <p class="text-muted fs-6 mb-0 mx-auto" style="max-width: 500px;">
                    يرجى إدخال رقم الهوية الوطنية، رقم الملف، أو اسم المريض في مربع البحث أعلاه للاستعلام عن كامل التسجيلات والمراجعات.
                </p>
            </div>
        </div>
    @endif

</div>

<style>
    .transition-all { transition: all 0.2s ease-in-out; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important; }
</style>
@endsection
