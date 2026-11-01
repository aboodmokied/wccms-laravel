@extends('layouts.app')

@section('title', 'التقييم الأولي للجروح')

@push('styles')
    @vite(['resources/css/pages/requests.css'])
@endpush

@section('content')
<div class="container-fluid py-3 initial-assessment-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h4 fw-bold mb-1">التقييم الأولي للجروح</h1><div class="text-muted">{{ $request->WC_REQ_PAT_NAME_SNAPSHOT }} · {{ $request->WC_REQ_PAT_ID_SNAPSHOT }}</div></div>
        <x-status-badge :status="$request->WC_REQ_STATUS" />
    </div>

    @if($errors->any())<div class="alert alert-danger"><strong>تعذر قبول التسجيل. أكمل بيانات الجروح:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-4"><span class="text-muted d-block small">الممرض المحول</span><strong>{{ $request->nurse?->full_name ?? 'لم يُسجل' }}</strong></div>
        <div class="col-md-8"><span class="text-muted d-block small">سبب الإحالة</span><strong>{{ $request->WC_REQ_NOTES ?: 'لا توجد ملاحظات' }}</strong></div>
    </div></div></div>

    @if(in_array($request->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS']))
    <form method="POST" enctype="multipart/form-data" action="{{ route('requests.accept', $request) }}" data-initial-assessment-form>
        @csrf
        <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body"><h2 class="h6 fw-bold mb-3">السياق المشترك للتسجيل</h2><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">القسم <span class="text-danger">*</span></label><select class="form-select" name="department_code" required><option value="">اختر</option>@foreach($departments as $item)<option value="{{ $item->WC_DEPT_CODE }}" @selected(old('department_code') === $item->WC_DEPT_CODE)>{{ $item->WC_DEPT_NAME }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">الممرض السريري <span class="text-danger">*</span></label><select class="form-select" name="clinic_nurse_code" required><option value="">اختر</option>@foreach($nurses as $item)<option value="{{ $item->EMP_SERIAL }}" @selected((string) old('clinic_nurse_code', $request->WC_REQ_REVIEWED_BY_CD) === (string) $item->EMP_SERIAL)>{{ $item->full_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">عضو فريق الجروح</label><select class="form-select" name="wct_employee_code"><option value="">بدون</option>@foreach($woundCareTeam as $item)<option value="{{ $item->EMP_SERIAL }}">{{ $item->full_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">الطبيب</label><select class="form-select" name="doctor_employee_code"><option value="">بدون</option>@foreach($doctors as $item)<option value="{{ $item->EMP_SERIAL }}">{{ $item->full_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label fw-semibold d-block"><bdi>{{ __('clinical.fields.allergies') }}</bdi></label><input type="hidden" name="has_allergies" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="has_allergies" value="1" @checked(old('has_allergies'))><span class="form-check-label">نعم</span></label></div>
            <div class="col-md-4"><label class="form-label fw-semibold"><bdi>{{ __('clinical.fields.allergies_description') }}</bdi></label><input class="form-control" name="allergies_description" value="{{ old('allergies_description') }}"></div>
            <div class="col-12"><label class="form-label fw-semibold d-block"><bdi>{{ __('clinical.fields.pmh') }}</bdi></label><input type="hidden" name="no_comorbidities" value="0"><label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="no_comorbidities" value="1" data-no-comorbidities @checked(old('no_comorbidities'))><span class="form-check-label">{{ __('clinical.no_comorbidities') }}</span></label>@foreach($pmhConditions as $item)<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="pmh_codes[]" value="{{ $item->WC_PMH_CODE }}" data-pmh @checked(in_array($item->WC_PMH_CODE, old('pmh_codes', []), true))><span class="form-check-label">{{ $item->WC_PMH_NAME }}</span></label>@endforeach</div>
            <div class="col-12"><label class="form-label fw-semibold"><bdi>{{ __('clinical.fields.pmh_notes') }}</bdi></label><textarea class="form-control" name="pmh_notes" rows="2">{{ old('pmh_notes') }}</textarea></div>
            <div class="col-md-4"><label class="form-label fw-semibold">تاريخ الزيارة القادمة</label><input class="form-control" type="date" min="{{ now()->toDateString() }}" name="next_visit_date" value="{{ old('next_visit_date') }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">تكرار المتابعة</label><input class="form-control" name="follow_up_frequency" value="{{ old('follow_up_frequency') }}" placeholder="مثال: كل أسبوع"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">هدف المتابعة <span class="text-danger">*</span></label><input class="form-control" name="follow_up_objective" value="{{ old('follow_up_objective') }}" required></div>
        </div></div></div>

        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">الجروح والتقييمات الأولية</h2><button class="btn btn-outline-primary" type="button" data-add-wound><i class="bi bi-plus-circle"></i> إضافة جرح</button></div>
        <div class="d-grid gap-4" data-wounds-container>
            @for($index = 0; $index < max(1, count(old('diagnoses', []))); $index++)
                <article class="card border-0 shadow-sm rounded-4 wound-card" data-wound-card><div class="card-header bg-white d-flex justify-content-between"><strong data-wound-title>الجرح {{ $index + 1 }}</strong><button type="button" class="btn btn-sm btn-outline-danger" data-remove-wound @disabled($index === 0)>حذف</button></div><div class="card-body">@include('wound_assessments.partials.fields', ['fieldPrefix' => "diagnoses[{$index}]", 'initial' => true])</div></article>
            @endfor
        </div>

        <div class="d-flex flex-wrap justify-content-between gap-2 mt-4"><button class="btn btn-success btn-lg" type="submit">قبول الطلب وحفظ الملف الطبي</button><button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#ineligibleModal">عدم الأهلية</button></div>
    </form>
    @else
        <div class="alert alert-secondary">تم البت في هذا الطلب سابقاً.</div>
    @endif
</div>

<div class="modal fade" id="ineligibleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('requests.ineligible', $request) }}">@csrf<div class="modal-header"><h2 class="modal-title h5">سبب عدم الأهلية</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><textarea class="form-control" name="reason" rows="4" required></textarea><input type="hidden" name="clinic_nurse_code" value="{{ old('clinic_nurse_code', $request->WC_REQ_REVIEWED_BY_CD) }}"></div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">إلغاء</button><button class="btn btn-danger" type="submit">تأكيد</button></div></form></div></div></div>
@endsection


