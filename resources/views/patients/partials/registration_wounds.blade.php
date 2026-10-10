<section class="patient-clinical-section" aria-labelledby="wounds-{{ $registration->WC_REG_CODE }}-title">
    <header class="patient-clinical-section-heading">
        <div>
            <span class="patient-clinical-kicker">التشخيصات والصور الأولية</span>
            <h3 id="wounds-{{ $registration->WC_REG_CODE }}-title">الجروح المسجلة</h3>
        </div>
        <span class="patient-section-count">{{ $registration->diagnoses->count() }}</span>
    </header>

    <div class="patient-wound-list">
        @forelse($registration->diagnoses as $wound)
            @php
                $baselineId = 'baseline-' . $registration->WC_REG_CODE . '-' . $wound->WC_RDIAG_CODE;
                $woundLabel = $wound->woundType?->WC_WTYPE_NAME ?? 'جرح غير محدد';
                $diagnosisLabel = $wound->diagnosis?->WC_DIAG_NAME ?? 'دون تشخيص';
            @endphp
            <article class="patient-wound-card" id="{{ $baselineId }}" data-wound-code="{{ $wound->WC_RDIAG_CODE }}">
                <div class="patient-wound-heading">
                    <div>
                        <span class="patient-wound-number">جرح {{ $loop->iteration }}</span>
                        <h4>{{ $woundLabel }}</h4>
                        <p>{{ $diagnosisLabel }}</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">خط الأساس</span>
                </div>

                @if($wound->WC_RDIAG_DESCRIPTION)
                    <p class="patient-wound-description">{{ $wound->WC_RDIAG_DESCRIPTION }}</p>
                @endif

                <dl class="patient-audit-grid">
                    <div>
                        <dt>الموظف السريري</dt>
                        <dd>{{ $wound->clinicalEmployee?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</dd>
                    </div>
                    <div>
                        <dt>مدخل البيانات</dt>
                        <dd>{{ $wound->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</dd>
                    </div>
                </dl>

                @if($wound->woundPhotos->isNotEmpty())
                    <div class="patient-photo-grid">
                        @foreach($wound->woundPhotos as $photo)
                            <a
                                href="{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}"
                                target="_blank"
                                rel="noopener"
                                class="patient-photo"
                                data-photo-code="{{ $photo->WC_PHOTO_CODE }}"
                            >
                                <img
                                    src="{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}"
                                    alt="صورة خط الأساس لـ {{ $woundLabel }} — {{ $diagnosisLabel }}"
                                    loading="lazy"
                                >
                                <span class="patient-photo-meta">
                                    <strong>صورة البداية</strong>
                                    <small dir="ltr">{{ \Illuminate\Support\Carbon::parse($photo->WC_PHOTO_TAKEN_AT)->format('Y-m-d H:i') }}</small>
                                    <small>التقاط سريري: <strong>{{ $photo->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</strong></small>
                                    <small>إدخال الصورة: <strong>{{ $photo->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</strong></small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="patient-inline-empty">لا توجد صور خط أساس لهذا الجرح.</p>
                @endif
            </article>
        @empty
            <div class="patient-section-empty">
                <i class="bi bi-bandaid" aria-hidden="true"></i>
                <div>
                    <strong>لا توجد جروح مسجلة</strong>
                    <span>لم تُسجل تشخيصات جروح ضمن هذا التسجيل.</span>
                </div>
            </div>
        @endforelse
    </div>
</section>
