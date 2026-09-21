@extends('layouts.app')

@section('title', 'إكمال تسجيل مريض جديد')

@push('styles')
    @vite(['resources/css/pages/registrations.css'])
@endpush

@section('content')
<x-card title="إكمال تسجيل مريض وتحديد التشخيص الأولي" icon="bi-person-plus" subtitle="ربط المريض بالقسم وتوثيق التاريخ المرضي والضمادات والتشخيص الجراحي">
    <form method="POST" action="{{ url('/registrations') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <x-form-group label="اسم المريض" name="patient_name" value="سعود خالد المنصور" required="true" />
            </div>
            <div class="col-md-6">
                <x-form-group label="القسم الطبي المحول منه" name="department" value="العناية المركزة (ICU)" required="true" />
            </div>
        </div>

        <h6 class="fw-bold text-primary mt-3 mb-3 border-bottom pb-2">التشخيص الأولي والجرح</h6>
        <div class="row">
            <div class="col-md-6">
                <x-form-group label="التشخيص الجراحي/المرضي" name="diagnosis_name" required="true" />
            </div>
            <div class="col-md-6">
                <x-form-group label="نوع الجرح" name="wound_type" required="true" />
            </div>
        </div>

        <x-form-group label="وصف الجرح والموقع" name="description" type="textarea" />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ url('/registrations') }}" class="btn btn-light px-4">{{ __('ui.buttons.cancel') }}</a>
            <button type="submit" class="btn btn-success px-4">{{ __('ui.buttons.save') }}</button>
        </div>
    </form>
</x-card>
@endsection
