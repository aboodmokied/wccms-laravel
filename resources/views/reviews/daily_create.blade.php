@extends('layouts.app')

@section('title', $scheduledReview ? 'توثيق مراجعة مجدولة' : 'إدخال مراجعة يومية')

@push('styles')
    @vite(['resources/css/pages/reviews.css'])
@endpush

@section('content')
<div class="container-fluid py-3 structured-review-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h4 fw-bold mb-1">{{ $scheduledReview ? 'توثيق المراجعة المجدولة' : 'المراجعة اليومية' }}</h1><div class="text-muted">{{ $registration->patient?->MR_PAT_FULL_NAME }} · {{ $registration->diagnoses->count() }} جرح</div></div>
        <a class="btn btn-outline-primary" href="{{ route('patients.show', $registration->WC_REG_PATIENT_CD) }}"><i class="bi bi-clock-history"></i> عرض التاريخ الصحي</a>
    </div>

    @if($errors->any())<div class="alert alert-danger"><strong>لم يتم الحفظ. راجع الحقول التالية:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form method="POST" enctype="multipart/form-data" data-structured-review-form action="{{ $scheduledReview ? route('reviews.scheduled.store', $scheduledReview) : route('reviews.daily.store', $registration->WC_REG_CODE) }}">
        @csrf
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">

        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body"><h2 class="h6 fw-bold mb-3">بيانات الزيارة المشتركة</h2><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">الممرض السريري <span class="text-danger">*</span></label><select class="form-select" name="nurse_code" required><option value="">اختر</option>@foreach($employees as $item)<option value="{{ $item->PER_EMP_CODE }}" @selected(old('nurse_code') === $item->PER_EMP_CODE)>{{ $item->PER_EMP_FULL_NAME }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">عضو فريق الجروح</label><select class="form-select" name="wct_employee_code"><option value="">بدون</option>@foreach($employees as $item)<option value="{{ $item->PER_EMP_CODE }}" @selected(old('wct_employee_code') === $item->PER_EMP_CODE)>{{ $item->PER_EMP_FULL_NAME }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">الطبيب</label><select class="form-select" name="doctor_employee_code"><option value="">بدون</option>@foreach($employees as $item)<option value="{{ $item->PER_EMP_CODE }}" @selected(old('doctor_employee_code') === $item->PER_EMP_CODE)>{{ $item->PER_EMP_FULL_NAME }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">النتيجة الإجمالية <span class="text-danger">*</span></label><select class="form-select" name="overall_status" required><option value="IMPROVED_STAY">تحسن واستمرار</option><option value="IMPROVED_SCHEDULED">تحسن مع موعد</option><option value="IMPROVED_DISCHARGE">تحسن وخروج</option><option value="WORSENED_STAY">تدهور واستمرار</option><option value="WORSENED_SCHEDULED">تدهور مع موعد</option><option value="WORSENED_ADMISSION">تدهور ودخول المستشفى</option><option value="DEATH">وفاة</option></select></div>
            <div class="col-md-8"><label class="form-label fw-semibold">ملاحظات الزيارة</label><textarea class="form-control" name="general_notes" rows="2">{{ old('general_notes') }}</textarea></div>
        </div></div></div>

        <div class="d-grid gap-4 mb-4">
            @foreach($registration->diagnoses as $index => $diagnosis)
                @include('wound_assessments.partials.follow-up-fields', compact('diagnosis', 'index'))
            @endforeach
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body"><h2 class="h6 fw-bold mb-3">خطة المتابعة المشتركة</h2><div class="row g-3">
            <div class="col-md-4"><label class="form-label">تاريخ الزيارة القادمة</label><input type="date" min="{{ now()->toDateString() }}" class="form-control" name="next_visit_date" value="{{ old('next_visit_date') }}"></div>
            <div class="col-md-4"><label class="form-label">تكرار المتابعة</label><input class="form-control" name="follow_up_frequency" value="{{ old('follow_up_frequency') }}" placeholder="مثال: كل أسبوع"></div>
            <div class="col-md-4"><label class="form-label">هدف المتابعة</label><input class="form-control" name="follow_up_objective" value="{{ old('follow_up_objective') }}"></div>
        </div></div></div>

        <div class="d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ $scheduledReview ? route('reviews.scheduled') : route('reviews.daily.pending') }}">إلغاء</a><button class="btn btn-primary px-4" type="submit" data-submit-review>حفظ الزيارة</button></div>
    </form>
</div>
@endsection


