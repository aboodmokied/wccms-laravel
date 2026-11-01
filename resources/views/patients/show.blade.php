@extends('layouts.app')

@section('title', 'السجل الطبي - ' . $patient->full_name)

@push('styles')
    @vite([
        'resources/css/pages/registrations.css',
        'resources/css/pages/patients.css',
    ])
@endpush

@section('content')
<div class="container-fluid px-0 patient-record-page">
    <header class="patient-record-heading">
        <div>
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 rounded-3 mb-3">
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <span>العودة للبحث</span>
            </a>
            <p class="patient-record-eyebrow mb-1">السجل الصحي الطولي</p>
            <h1 class="wccms-page-heading mb-1">السجل الطبي الشامل للمريض</h1>
            <p class="text-muted mb-0">كل تسجيل يمثل فترة علاج مستقلة بجروحها ومواعيدها ومراجعاتها.</p>
        </div>
        <div class="patient-record-total" aria-label="إجمالي التسجيلات">
            <strong>{{ $patient->registrations->count() }}</strong>
            <span>تسجيل سريري</span>
        </div>
    </header>

    @include('patients.partials.patient_demographics_banner', ['selectedPatient' => $patient])

    <section class="patient-history" aria-labelledby="patient-history-title">
        <div class="patient-history-heading">
            <div>
                <p class="patient-record-eyebrow mb-1">مسار العلاج</p>
                <h2 class="fs-5 fw-bold mb-1" id="patient-history-title">سجل التسجيلات السريرية</h2>
                <p class="text-muted small mb-0">التسجيلات النشطة أولاً، ثم التسجيلات السابقة من الأحدث إلى الأقدم.</p>
            </div>
        </div>

        <div class="patient-registration-list">
            @forelse($patient->registrations as $registration)
                @include('patients.partials.registration_record', [
                    'registration' => $registration,
                    'isInitiallyOpen' => $registration->WC_REG_CODE === $initialRegistrationCode,
                ])
            @empty
                <div class="patient-record-empty">
                    <i class="bi bi-folder2-open" aria-hidden="true"></i>
                    <h3>لا توجد تسجيلات سريرية لهذا المريض</h3>
                    <p>بيانات المريض متاحة، لكن لم يُفتح له ملف في عيادة العناية بالجروح بعد.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
