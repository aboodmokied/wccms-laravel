@if($assessment)
    <div class="latest-assessment rounded-3 border bg-white p-3 mb-3">
        <div class="fw-semibold mb-2">آخر تقييم — {{ $assessment->WC_WA_ASSESSED_AT?->format('Y-m-d H:i') }}</div>
        <div class="row g-2 small">
            <div class="col-md-3">الحالة: {{ $assessment->woundStatus?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</div>
            <div class="col-md-3">القياس: {{ $assessment->WC_WA_LENGTH_MM ?? '—' }} × {{ $assessment->WC_WA_WIDTH_MM ?? '—' }} × {{ $assessment->WC_WA_DEPTH_MM ?? '—' }} mm</div>
            <div class="col-md-3">الإفرازات: {{ $assessment->exudateAmount?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</div>
            <div class="col-md-3">الألم: {{ $assessment->painSeverity?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</div>
        </div>
    </div>
@else
    <div class="alert alert-secondary py-2">لا يوجد تقييم منظم سابق لهذا الجرح.</div>
@endif
