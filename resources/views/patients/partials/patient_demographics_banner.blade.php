<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-top border-4 border-primary">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-auto text-center text-md-start">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold display-6" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
            <div class="col-12 col-md">
                <h4 class="fw-bolder fs-4 mb-3 text-dark">{{ $selectedPatient->MR_PAT_FULL_NAME }}</h4>
                <div class="d-flex flex-wrap gap-2 fs-7 text-muted">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-8"><i class="bi bi-person-vcard text-primary me-2"></i>هوية: {{ $selectedPatient->MR_PAT_ID_NO }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-8"><i class="bi bi-folder text-primary me-2"></i>ملف: {{ $selectedPatient->MR_PAT_CODE }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-8"><i class="bi bi-gender-ambiguous text-primary me-2"></i>الجنس: {{ $selectedPatient->MR_PAT_GENDER === 'M' ? 'ذكر' : 'أنثى' }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-8"><i class="bi bi-calendar3 text-primary me-2"></i>العمر: {{ \Carbon\Carbon::parse($selectedPatient->MR_PAT_DOB)->age }} سنة</span>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-8"><i class="bi bi-telephone text-primary me-2"></i>جوال: {{ $selectedPatient->MR_PAT_MOBILE_NO ?? 'غير متوفر' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
