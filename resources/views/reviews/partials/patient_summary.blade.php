<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden bg-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5 shadow-2xs border border-primary-subtle" style="width: 44px; height: 44px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
            <div class="col-12 col-md">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="fw-bold fs-5 mb-0 text-dark">{{ $registration->patient?->full_name }}</h2>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-8 rounded-pill">
                        <i class="bi bi-card-text me-1"></i>رقم الهوية: {{ $registration->patient?->MRP_ID }}
                    </span>
                    @if($registration->WC_REG_STATUS === 'ACTIVE')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-8 rounded-pill">
                            <i class="bi bi-circle-fill me-1 fs-8"></i>تسجيل نشط
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 fs-8 rounded-pill">
                            <i class="bi bi-box-arrow-right me-1 fs-8"></i>مُخرج (مغلق)
                        </span>
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-7 mt-2">
                    <span><i class="bi bi-gender-ambiguous me-1 text-primary"></i>الجنس: <strong>{{ $registration->patient?->sex_label ?: 'غير معروف' }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-building me-1 text-primary"></i>القسم: <strong>{{ $registration->department?->WC_DEPT_NAME ?? 'عيادة الجروح' }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-person-badge me-1 text-primary"></i>الممرض المسؤول: <strong>{{ $registration->nurse?->full_name ?? 'غير محدد' }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-calendar3 me-1 text-primary"></i>تاريخ التسجيل: <strong>{{ \Carbon\Carbon::parse($registration->WC_REG_DATE)->format('Y-m-d') }}</strong></span>
                </div>
            </div>
            
            <!-- Quick Clinical Metric Cards -->
            <div class="col-12 col-lg-auto border-top border-lg-0 pt-3 pt-lg-0 mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <div class="p-2.5 px-3 rounded-3 bg-light border text-center">
                        <span class="d-block fs-8 text-muted">عدد الزيارات</span>
                        <strong class="fs-6 text-primary">{{ $registration->dailyReviews->count() }}</strong>
                    </div>
                    <div class="p-2.5 px-3 rounded-3 bg-light border text-center">
                        <span class="d-block fs-8 text-muted">الجروح المسجلة</span>
                        <strong class="fs-6 text-dark">{{ $registration->diagnoses->count() }}</strong>
                    </div>
                    <div class="p-2.5 px-3 rounded-3 bg-light border text-center">
                        <span class="d-block fs-8 text-muted">آخر زيارة</span>
                        <strong class="fs-7 text-dark">
                            @if($registration->dailyReviews->first())
                                {{ \Carbon\Carbon::parse($registration->dailyReviews->first()->WC_DREV_VISIT_DATE)->format('m-d') }}
                            @else
                                لا يوجد
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

