<section class="patient-clinical-section" aria-labelledby="appointments-{{ $registration->WC_REG_CODE }}-title">
    <header class="patient-clinical-section-heading">
        <div>
            <span class="patient-clinical-kicker">خطة المتابعة</span>
            <h3 id="appointments-{{ $registration->WC_REG_CODE }}-title">المواعيد المجدولة</h3>
        </div>
        <span class="patient-section-count">{{ $registration->scheduledReviews->count() }}</span>
    </header>

    <div class="patient-appointment-list">
        @forelse($registration->scheduledReviews as $appointment)
            <article class="patient-appointment" data-appointment-code="{{ $appointment->WC_SREV_CODE }}">
                <div class="patient-appointment-date">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    <div>
                        <strong dir="ltr">{{ \Illuminate\Support\Carbon::parse($appointment->WC_SREV_SCHEDULED_DATE)->format('Y-m-d') }}</strong>
                        <span dir="ltr">{{ \Illuminate\Support\Carbon::parse($appointment->WC_SREV_SCHEDULED_DATE)->format('H:i') }}</span>
                    </div>
                </div>
                <div class="patient-appointment-content">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <x-status-badge :status="$appointment->WC_SREV_STATUS" />
                        <span class="text-muted small">المراجعة رقم {{ $appointment->WC_SREV_VISIT_SERIAL }}</span>
                        @if($appointment->dailyReview)
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">تم توثيق الزيارة</span>
                        @endif
                    </div>
                    <p>{{ $appointment->WC_SREV_NOTES ?: 'لا توجد ملاحظات لهذا الموعد.' }}</p>
                    <small>أنشأه: <strong>{{ $appointment->createdBy?->PER_USER_USERNAME ?? 'النظام' }}</strong></small>
                </div>
            </article>
        @empty
            <div class="patient-section-empty">
                <i class="bi bi-calendar2-x" aria-hidden="true"></i>
                <div>
                    <strong>لا توجد مواعيد مجدولة</strong>
                    <span>لم تُسجل مواعيد متابعة ضمن هذا التسجيل.</span>
                </div>
            </div>
        @endforelse
    </div>
</section>
