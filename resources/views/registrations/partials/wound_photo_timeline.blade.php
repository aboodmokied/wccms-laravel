<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-images text-primary fs-5"></i>
            <h6 class="fw-bold mb-0 text-dark fs-6">معرض وخط السجل البصري لتطور الجروح (Wound Photo Progression)</h6>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fs-7">
            توثيق التئام الشفاء بصرياً
        </span>
    </div>

    <div class="card-body p-4">
        @php
            $diagnosesWithPhotos = $registration->diagnoses->filter(function($diag) {
                return \App\Models\WcWoundPhoto::where('WC_PHOTO_REG_DIAGNOSIS_CD', $diag->WC_RDIAG_CODE)->exists();
            });
        @endphp

        @if($diagnosesWithPhotos->isEmpty())
            <div class="text-center py-5 bg-light-subtle rounded-3 border border-dashed">
                <i class="bi bi-camera text-muted fs-1 d-block mb-2"></i>
                <h6 class="fw-bold text-muted mb-1">لا توجد صور موثقة للجروح حتى الآن</h6>
                <p class="text-muted fs-7 mb-0">يمكن توثيق صور الجروح عند القبول أو عند إجراء مراجعات وتقييمات سريرية يومية للمريض.</p>
            </div>
        @else
            <div class="accordion accordion-flush" id="woundPhotosAccordion">
                @foreach($diagnosesWithPhotos as $idx => $diag)
                    @php
                        $photos = \App\Models\WcWoundPhoto::with(['nurse'])
                            ->where('WC_PHOTO_REG_DIAGNOSIS_CD', $diag->WC_RDIAG_CODE)
                            ->orderBy('WC_PHOTO_TAKEN_AT', 'asc')
                            ->get();

                        $initialPhoto = $photos->firstWhere('WC_PHOTO_STAGE', 'INITIAL') ?? $photos->first();
                    @endphp

                    <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-2xs">
                        <h2 class="accordion-header" id="headingDiag{{ $idx }}">
                            <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }} bg-light fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiag{{ $idx }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="collapseDiag{{ $idx }}">
                                <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill px-2.5 py-1 fs-8">جرح #{{ $idx + 1 }}</span>
                                        <span>{{ $diag->woundType?->WC_WTYPE_NAME }} — <span class="text-primary">{{ $diag->diagnosis?->WC_DIAG_NAME }}</span></span>
                                    </div>
                                    <span class="badge bg-secondary text-white rounded-pill fs-8 px-2.5">{{ $photos->count() }} صورة موثقة</span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseDiag{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" aria-labelledby="headingDiag{{ $idx }}" data-bs-parent="#woundPhotosAccordion">
                            <div class="accordion-body p-4 bg-white">
                                <div class="row g-4">
                                    @foreach($photos as $photo)
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                            <div class="card h-100 border shadow-2xs rounded-3 overflow-hidden transition-all hover-shadow">
                                                <div class="position-relative bg-dark d-flex align-items-center justify-content-center" style="height: 180px;">
                                                    <img src="{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}" class="w-100 h-100 object-fit-cover" alt="صورة الجرح" loading="lazy">
                                                    
                                                    <div class="position-absolute top-0 start-0 m-2">
                                                        @if($photo->WC_PHOTO_STAGE === 'INITIAL')
                                                            <span class="badge bg-success text-white shadow-sm rounded-pill px-2.5 py-1 fs-8">
                                                                <i class="bi bi-flag-fill me-1"></i>صورة أوليّة (Baseline)
                                                            </span>
                                                        @else
                                                            <span class="badge bg-info text-white shadow-sm rounded-pill px-2.5 py-1 fs-8">
                                                                <i class="bi bi-arrow-repeat me-1"></i>مراجعة متابعة
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="card-body p-3 fs-8">
                                                    <div class="d-flex align-items-center justify-content-between text-muted mb-1">
                                                        <span><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($photo->WC_PHOTO_TAKEN_AT)->format('Y-m-d H:i') }}</span>
                                                    </div>
                                                    <div class="text-dark fw-bold mb-2">
                                                        <i class="bi bi-person-badge text-primary me-1"></i>{{ $photo->nurse?->PER_EMP_FULL_NAME ?? 'ممرض العيادة' }}
                                                    </div>

                                                    <div class="d-flex gap-1 mt-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm w-100 fs-8 d-flex align-items-center justify-content-center gap-1"
                                                                onclick="openPhotoModal('{{ route('wound_photos.show', $photo->WC_PHOTO_CODE) }}', '{{ route('wound_photos.show', $initialPhoto->WC_PHOTO_CODE) }}', '{{ \Carbon\Carbon::parse($photo->WC_PHOTO_TAKEN_AT)->format('Y-m-d H:i') }}', '{{ $photo->WC_PHOTO_STAGE }}')">
                                                            <i class="bi bi-eye-fill"></i>
                                                            <span>مقاينة ومقارنة</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Lightbox & Side-by-Side Comparison Modal -->
<div class="modal fade" id="woundPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-layout-split text-info"></i>
                    <span>معاينة ومقارنة التطور البصري للجرح</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-4 text-center">
                    <!-- Left Column: Baseline Initial Photo -->
                    <div class="col-md-6 border-end">
                        <div class="p-2 bg-white rounded-3 shadow-2xs">
                            <span class="badge bg-success mb-2 px-3 py-1 fs-7">الصورة الأولية لخط الأساس (Initial Baseline)</span>
                            <div class="bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="min-height: 300px; max-height: 450px;">
                                <img id="modalBaselineImg" src="" class="img-fluid object-fit-contain w-100 h-100" alt="صورة أولية">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Current Selected Photo -->
                    <div class="col-md-6">
                        <div class="p-2 bg-white rounded-3 shadow-2xs">
                            <span class="badge bg-info mb-2 px-3 py-1 fs-7" id="modalCurrentStageBadge">الصورة المختارة</span>
                            <div class="bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="min-height: 300px; max-height: 450px;">
                                <img id="modalCurrentImg" src="" class="img-fluid object-fit-contain w-100 h-100" alt="صورة مختارة">
                            </div>
                            <span class="d-block text-muted fs-8 mt-2" id="modalCurrentDate"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openPhotoModal(currentUrl, baselineUrl, dateStr, stageStr) {
    document.getElementById('modalBaselineImg').src = baselineUrl;
    document.getElementById('modalCurrentImg').src = currentUrl;
    document.getElementById('modalCurrentDate').textContent = 'تاريخ التوثيق: ' + dateStr;
    document.getElementById('modalCurrentStageBadge').textContent = (stageStr === 'INITIAL') ? 'الصورة الأولية لخط الأساس' : 'صورة مراجعة المتابعة (' + dateStr + ')';

    const modal = new bootstrap.Modal(document.getElementById('woundPhotoModal'));
    modal.show();
}
</script>
