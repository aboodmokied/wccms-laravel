@php
    $isActive = $registration->WC_REG_STATUS === 'ACTIVE';
    $registrationId = 'registration-' . $registration->WC_REG_CODE;
    $registrationTitleId = $registrationId . '-title';
@endphp

<details
    class="patient-registration {{ $isActive ? 'is-active' : 'is-historical' }}"
    id="{{ $registrationId }}"
    data-registration-code="{{ $registration->WC_REG_CODE }}"
    data-registration-status="{{ $registration->WC_REG_STATUS }}"
    data-initially-open="{{ $isInitiallyOpen ? 'true' : 'false' }}"
    @if($isInitiallyOpen) open @endif
>
    <summary class="patient-registration-summary" aria-labelledby="{{ $registrationTitleId }}">
        <span class="patient-registration-marker" aria-hidden="true">
            <i class="bi {{ $isActive ? 'bi-activity' : 'bi-archive' }}"></i>
        </span>

        <span class="patient-registration-primary">
            <span class="d-flex flex-wrap align-items-center gap-2">
                <strong class="patient-registration-title" id="{{ $registrationTitleId }}">
                    {{ $isActive ? 'التسجيل الحالي' : 'تسجيل سابق' }}
                </strong>
                <x-status-badge :status="$registration->WC_REG_STATUS" />
            </span>
            <span class="patient-registration-date" dir="ltr">
                {{ \Illuminate\Support\Carbon::parse($registration->WC_REG_DATE)->format('Y-m-d H:i') }}
            </span>
            <span class="patient-registration-code" dir="ltr">{{ $registration->WC_REG_CODE }}</span>
        </span>

        <span class="patient-registration-context">
            <span>
                <small>القسم</small>
                <strong>{{ $registration->department?->WC_DEPT_NAME ?? 'غير محدد' }}</strong>
            </span>
            <span>
                <small>الممرض المسؤول</small>
                <strong>{{ $registration->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong>
            </span>
            <span>
                <small><bdi>{{ __('clinical.fields.procedure') }}</bdi></small>
                <strong>{{ $registration->procedure?->WC_PROC_NAME ?? 'غير محدد' }}</strong>
            </span>
            @if($registration->WC_REG_DISCHARGED_AT)
                <span>
                    <small>تاريخ الخروج</small>
                    <strong dir="ltr">{{ \Illuminate\Support\Carbon::parse($registration->WC_REG_DISCHARGED_AT)->format('Y-m-d H:i') }}</strong>
                </span>
            @endif
        </span>

        <span class="patient-registration-counts" aria-label="ملخص محتوى التسجيل">
            <span><strong>{{ $registration->DIAGNOSES_COUNT }}</strong> جروح</span>
            <span><strong>{{ $registration->DAILY_REVIEWS_COUNT }}</strong> مراجعات</span>
            <span><strong>{{ $registration->SCHEDULED_REVIEWS_COUNT }}</strong> مواعيد</span>
        </span>

        <a
            href="{{ route('registrations.show', $registration->WC_REG_CODE) }}"
            class="patient-registration-link"
            aria-label="فتح الملف التفصيلي للتسجيل {{ $registration->WC_REG_CODE }}"
        >
            <i class="bi bi-box-arrow-up-left" aria-hidden="true"></i>
            <span>فتح الملف التفصيلي</span>
        </a>

        <i class="bi bi-chevron-down patient-registration-chevron" aria-hidden="true"></i>
    </summary>

    <div class="patient-registration-body">
        <div class="patient-registration-overview">
            <div>
                <span><bdi>{{ __('clinical.fields.pmh_notes') }}</bdi></span>
                <p>{{ $registration->WC_REG_PMH_NOTES ?: 'لا توجد ملاحظات تاريخ مرضي مسجلة.' }}</p>
            </div>
            <div>
                <span>مدخل بيانات التسجيل</span>
                <p>{{ $registration->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</p>
            </div>
            @if(!$isActive)
                <div>
                    <span>منفذ التخريج</span>
                    <p>{{ $registration->dischargingNurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</p>
                </div>
            @endif
        </div>

        <div class="patient-registration-sections">
            @include('patients.partials.registration_wounds', ['registration' => $registration])
            @include('patients.partials.registration_appointments', ['registration' => $registration])

            @include('reviews.partials.review_timeline', [
                'registration' => $registration,
                'sectionId' => $registrationId . '-review-history',
                'showActions' => false,
                'showBaselinePhotos' => false,
                'openFirstReview' => false,
                'baselineAnchorPrefix' => 'baseline-' . $registration->WC_REG_CODE,
            ])
        </div>
    </div>
</details>

