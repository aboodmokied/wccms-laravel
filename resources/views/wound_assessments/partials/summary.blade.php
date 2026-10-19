@php
    $structured = $assessment ?? null;
    $term = fn (string $field): string => __("clinical.fields.{$field}");
    $choice = fn ($item): string => $item?->displayLabel() ?? 'لم يُسجل';
@endphp
<section class="assessment-summary border rounded-3 p-3 mb-3">
    @if($structured)
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <strong>{{ $structured->WC_WA_TYPE === 'INITIAL' ? 'التقييم الأولي' : 'تقييم المتابعة' }}</strong>
            <time class="text-muted">{{ $structured->WC_WA_ASSESSED_AT?->format('Y-m-d H:i') }}</time>
        </div>
        <dl class="row mb-0 small">
            <dt class="col-sm-3"><bdi>{{ $term('wound_status') }}</bdi></dt><dd class="col-sm-3"><bdi>{{ $choice($structured->woundStatus) }}</bdi></dd>
            <dt class="col-sm-3"><bdi>{{ $term('measurements') }}</bdi></dt><dd class="col-sm-3"><bdi>{{ $structured->WC_WA_LENGTH_MM ?? '—' }} × {{ $structured->WC_WA_WIDTH_MM ?? '—' }} × {{ $structured->WC_WA_DEPTH_MM ?? '—' }} mm</bdi></dd>
            <dt class="col-sm-3"><bdi>{{ $term('wound_bed') }} / {{ $term('wound_margin') }}</bdi></dt><dd class="col-sm-9"><bdi>{{ $structured->findings->map(fn ($finding) => $finding->choice?->displayLabel())->filter()->join(', ') ?: 'لم يُسجل' }}</bdi></dd>
            <dt class="col-sm-3"><bdi>{{ $term('exudate_amount') }} / {{ $term('exudate_type') }}</bdi></dt><dd class="col-sm-3"><bdi>{{ $choice($structured->exudateAmount) }} / {{ $choice($structured->exudateType) }}</bdi></dd>
            <dt class="col-sm-3"><bdi>{{ $term('pain_severity') }} / {{ $term('odor') }}</bdi></dt><dd class="col-sm-3"><bdi>{{ $choice($structured->painSeverity) }} / {{ $choice($structured->odor) }}</bdi></dd>
            <dt class="col-sm-3"><bdi>{{ $term('treatment') }}</bdi></dt><dd class="col-sm-9"><bdi>{{ collect([$structured->dressing?->WC_DRESS_NAME, $structured->medication?->WC_MED_NAME, $structured->procedure?->WC_PROC_NAME])->filter()->join(', ') }}</bdi>@if($structured->WC_WA_TREATMENT_NOTES) — {{ $structured->WC_WA_TREATMENT_NOTES }}@endif @if(! $structured->dressing && ! $structured->medication && ! $structured->procedure && ! $structured->WC_WA_TREATMENT_NOTES)لم يُسجل@endif</dd>
            @if($structured->reviewDetail)<dt class="col-sm-3"><bdi>{{ $term('wound_outcome') }}</bdi></dt><dd class="col-sm-9"><bdi>{{ $structured->reviewDetail->WC_DREVD_STATUS ? __('clinical.wound_outcomes.'.$structured->reviewDetail->WC_DREVD_STATUS) : 'لم يُسجل' }}</bdi></dd>@endif
            <dt class="col-sm-3">المسؤولية</dt><dd class="col-sm-9">الفاحص: {{ $structured->clinicalEmployee?->PER_EMP_FULL_NAME ?? 'لم يُسجل' }} · مدخل البيانات: {{ $structured->entryUser?->PER_USER_USERNAME ?? 'لم يُسجل' }}</dd>
        </dl>
        @if($structured->woundPhotos->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-3">@foreach($structured->woundPhotos as $photo)<a href="{{ route('wound_photos.show', $photo) }}" target="_blank" rel="noopener"><img src="{{ route('wound_photos.show', $photo) }}" alt="صورة الجرح" class="rounded border" width="112" height="84" style="object-fit:cover"></a>@endforeach</div>
        @endif
    @else
        <span class="text-muted">لم يُسجل تقييم منظم لهذه الزيارة؛ تظهر البيانات السردية القديمة أدناه.</span>
    @endif
</section>
