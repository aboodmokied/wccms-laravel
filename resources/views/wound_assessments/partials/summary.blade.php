@php($structured = $assessment ?? null)
<section class="assessment-summary border rounded-3 p-3 mb-3">
    @if($structured)
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <strong>{{ $structured->WC_WA_TYPE === 'INITIAL' ? 'التقييم الأولي' : 'تقييم المتابعة' }}</strong>
            <time class="text-muted">{{ $structured->WC_WA_ASSESSED_AT?->format('Y-m-d H:i') }}</time>
        </div>
        <dl class="row mb-0 small">
            <dt class="col-sm-3">حالة الجرح</dt><dd class="col-sm-3">{{ $structured->woundStatus?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</dd>
            <dt class="col-sm-3">القياسات</dt><dd class="col-sm-3">{{ $structured->WC_WA_LENGTH_MM ?? '—' }} × {{ $structured->WC_WA_WIDTH_MM ?? '—' }} × {{ $structured->WC_WA_DEPTH_MM ?? '—' }} mm</dd>
            <dt class="col-sm-3">سرير وحواف الجرح</dt><dd class="col-sm-9">{{ $structured->findings->pluck('choice.WC_ACH_LABEL_AR')->filter()->join('، ') ?: 'لم يُسجل' }}</dd>
            <dt class="col-sm-3">الإفرازات</dt><dd class="col-sm-3">{{ $structured->exudateAmount?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }} / {{ $structured->exudateType?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</dd>
            <dt class="col-sm-3">الألم والرائحة</dt><dd class="col-sm-3">{{ $structured->painSeverity?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }} / {{ $structured->odor?->WC_ACH_LABEL_AR ?? 'لم يُسجل' }}</dd>
            <dt class="col-sm-3">العلاج</dt><dd class="col-sm-9">{{ collect([$structured->dressing?->WC_DRESS_NAME, $structured->medication?->WC_MED_NAME, $structured->procedure?->WC_PROC_NAME, $structured->WC_WA_TREATMENT_NOTES])->filter()->join('، ') ?: 'لم يُسجل' }}</dd>
            @if($structured->reviewDetail)<dt class="col-sm-3">النتيجة</dt><dd class="col-sm-9">{{ $structured->reviewDetail->WC_DREVD_STATUS ?? 'لم يُسجل' }}</dd>@endif
            <dt class="col-sm-3">المسؤولية</dt><dd class="col-sm-9">الفاحص: {{ $structured->clinicalEmployee?->PER_EMP_FULL_NAME ?? 'لم يُسجل' }} · مدخل البيانات: {{ $structured->entryUser?->PER_USER_USERNAME ?? 'لم يُسجل' }}</dd>
        </dl>
        @if($structured->woundPhotos->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-3">@foreach($structured->woundPhotos as $photo)<a href="{{ route('wound_photos.show', $photo) }}" target="_blank" rel="noopener"><img src="{{ route('wound_photos.show', $photo) }}" alt="صورة الجرح" class="rounded border" width="112" height="84" style="object-fit:cover"></a>@endforeach</div>
        @endif
    @else
        <span class="text-muted">لم يُسجل تقييم منظم لهذه الزيارة؛ تظهر البيانات السردية القديمة أدناه.</span>
    @endif
</section>
