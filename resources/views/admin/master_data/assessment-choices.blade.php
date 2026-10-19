@extends('layouts.app')

@section('title', 'خيارات التقييم السريري')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h4 fw-bold mb-1">خيارات التقييم السريري</h1><p class="text-muted mb-0">تعديل الاسم الإنجليزي (المعروض في كل الصفحات) والتسمية العربية والترتيب والحالة. الفئة والمفتاح ثابتان لحماية السجل التاريخي.</p></div></div>
    @include('admin.master_data.partials.domain-nav')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="GET" class="card card-body border-0 shadow-sm mb-3"><div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label">الفئة</label><select class="form-select" name="category"><option value="">كل الفئات</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ __("clinical.choice_categories.{$category}") }}</option>@endforeach</select></div><div class="col-auto"><button class="btn btn-primary">تصفية</button></div></div></form>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>الفئة</th><th>المفتاح الثابت</th><th>الاسم الإنجليزي / التسمية العربية</th><th>الترتيب</th><th>الحالة</th><th></th></tr></thead><tbody>
        @forelse($choices as $choice)<tr>
            <td><code>{{ $choice->WC_ACH_CATEGORY }}</code></td><td><code>{{ $choice->WC_ACH_KEY }}</code></td>
            <td colspan="2"><form class="row g-2" method="POST" action="{{ route('admin.assessment_choices.update', $choice) }}">@csrf @method('PUT')<div class="col"><input class="form-control" name="label_en" dir="ltr" maxlength="150" aria-label="الاسم الإنجليزي" value="{{ $choice->WC_ACH_LABEL_EN }}" required></div><div class="col"><input class="form-control" name="label_ar" maxlength="150" aria-label="التسمية العربية" value="{{ $choice->WC_ACH_LABEL_AR }}" required></div><div class="col-3"><input class="form-control" type="number" min="0" name="display_order" value="{{ $choice->WC_ACH_DISPLAY_ORDER }}" required></div><div class="col-auto"><button class="btn btn-outline-primary">حفظ</button></div></form></td>
            <td><span class="badge {{ $choice->WC_ACH_IS_ACTIVE ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $choice->WC_ACH_IS_ACTIVE ? 'فعال' : 'معطل' }}</span></td>
            <td><form method="POST" action="{{ route('admin.assessment_choices.toggle', $choice) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $choice->WC_ACH_IS_ACTIVE ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $choice->WC_ACH_IS_ACTIVE ? 'تعطيل' : 'تفعيل' }}</button></form></td>
        </tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">لا توجد خيارات.</td></tr>@endforelse
    </tbody></table></div><div class="card-footer">{{ $choices->links() }}</div></div>
</div>
@endsection
