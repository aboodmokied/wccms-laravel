<!-- Modal: Add Daily Review -->
<div class="modal fade" id="addDailyReviewModal" tabindex="-1" aria-labelledby="addDailyReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient bg-primary text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center">
                        <i class="bi bi-clipboard2-pulse fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bolder fs-6 mb-0 text-white" id="addDailyReviewModalLabel">
                            توثيق التقييم والزيارة اليومية للجروح
                        </h5>
                        <span class="fs-8 text-white-50">تاريخ اليوم: {{ now()->format('Y-m-d') }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            
            <form action="{{ route('registrations.daily-reviews.store', $registration->WC_REG_CODE) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-light-subtle">
                    
                    <!-- SECTION 1: ATTENDING NURSE & DATA-ENTRY AUDIT -->
                    <div class="card border-0 shadow-xs rounded-3 mb-4 bg-white">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-7">
                                    <label class="form-label fw-bold text-dark fs-7 mb-1">
                                        <i class="bi bi-person-badge text-primary me-1"></i>الممرض المباشر للزيارة <span class="text-danger">*</span>
                                    </label>
                                    <select name="nurse_code" class="form-select fs-7 border-primary-subtle shadow-none" required>
                                        <option value="">اختر الممرض المباشر...</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->PER_EMP_CODE }}" {{ (Auth::user()->employee?->PER_EMP_CODE === $emp->PER_EMP_CODE) ? 'selected' : '' }}>
                                                {{ $emp->PER_EMP_FULL_NAME }} ({{ $emp->PER_EMP_JOB_TITLE ?? 'ممرض' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-5">
                                    <div class="p-2.5 bg-primary-subtle bg-opacity-30 rounded-3 border border-primary-subtle d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check text-primary fs-5"></i>
                                        <div>
                                            <span class="d-block text-muted fs-8">مُدخل البيانات (Audit):</span>
                                            <span class="fw-bold text-dark fs-7">{{ Auth::user()->PER_USER_USERNAME }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: OVERALL VISIT OUTCOME -->
                    <div class="card border-0 shadow-xs rounded-3 mb-4 bg-white">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark fs-7 mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
                                <i class="bi bi-check2-circle text-primary fs-5"></i>
                                <span>النتيجة الإجمالية للزيارة</span>
                                <span class="badge bg-primary-subtle text-primary fs-8 rounded-pill px-2.5">خيار واحد مطلوب</span>
                            </h6>
                            
                            <div class="row g-3">
                                <!-- Option 1: WORSENED_STAY (Default active care) -->
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="outcome-card border rounded-3 p-3 bg-white h-100 d-block cursor-pointer transition-all border-primary-subtle">
                                        <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1" type="radio" name="overall_status" id="overall_worsened_stay" value="WORSENED_STAY" checked>
                                            <div>
                                                <span class="fw-bold text-dark d-block fs-7 mb-1">
                                                    <i class="bi bi-arrow-repeat text-warning me-1"></i>استمرار المتابعة
                                                </span>
                                                <small class="text-muted d-block fs-8 lh-sm">استمرار العلاج والتقييم اليومي في العيادة.</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Option 2: IMPROVED_DISCHARGE -->
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="outcome-card border rounded-3 p-3 bg-white h-100 d-block cursor-pointer transition-all">
                                        <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1" type="radio" name="overall_status" id="overall_improved_discharge" value="IMPROVED_DISCHARGE">
                                            <div>
                                                <span class="fw-bold text-success d-block fs-7 mb-1">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>شفاء وتخريج نهائي
                                                </span>
                                                <small class="text-muted d-block fs-8 lh-sm">اكتمل التئام الجروح وإغلاق ملف التسجيل.</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Option 3: IMPROVED_SCHEDULED -->
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="outcome-card border rounded-3 p-3 bg-white h-100 d-block cursor-pointer transition-all">
                                        <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1" type="radio" name="overall_status" id="overall_improved_scheduled" value="IMPROVED_SCHEDULED">
                                            <div>
                                                <span class="fw-bold text-info d-block fs-7 mb-1">
                                                    <i class="bi bi-calendar-check-fill text-info me-1"></i>تخريج وموعد مراجعة
                                                </span>
                                                <small class="text-muted d-block fs-8 lh-sm">تخريج المريض وتحديد موعد مراجعة قادم.</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Option 4: DEATH -->
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="outcome-card border rounded-3 p-3 bg-white h-100 d-block cursor-pointer transition-all">
                                        <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1" type="radio" name="overall_status" id="overall_death" value="DEATH">
                                            <div>
                                                <span class="fw-bold text-secondary d-block fs-7 mb-1">
                                                    <i class="bi bi-x-circle-fill text-dark me-1"></i>حالة وفاة
                                                </span>
                                                <small class="text-muted d-block fs-8 lh-sm">توثيق وفاة المريض وإغلاق الملف.</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Scheduled Date Picker (Shown when IMPROVED_SCHEDULED selected) -->
                            <div class="mt-3 p-3 bg-info-subtle border border-info-subtle rounded-3 d-none" id="scheduledDateContainer">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-bold text-dark fs-7 mb-0">
                                            <i class="bi bi-calendar-plus text-info me-1"></i>تاريخ موعد المراجعة القادم:
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <input type="date" name="scheduled_date" class="form-select fs-7" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PER-WOUND EVALUATION (OPEN CLEAR CARDS) -->
                    <div class="card border-0 shadow-xs rounded-3 mb-4 bg-white">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark fs-7 mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bandaid text-primary fs-5"></i>
                                    <span>تقييم الجروح النشطة للمريض</span>
                                </div>
                                <span class="badge bg-primary text-white fs-8 rounded-pill">{{ $registration->diagnoses->count() }} جرح</span>
                            </h6>

                            <div class="d-flex flex-column gap-3">
                                @foreach($registration->diagnoses as $idx => $diag)
                                    <div class="border rounded-3 p-3 bg-light-subtle position-relative shadow-2xs">
                                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fs-8 fw-bold">جرح #{{ $idx + 1 }}</span>
                                                <h6 class="fw-bold fs-7 mb-0 text-dark">
                                                    {{ $diag->woundType?->WC_WTYPE_NAME }} — <span class="text-primary">{{ $diag->diagnosis?->WC_DIAG_NAME }}</span>
                                                </h6>
                                            </div>
                                        </div>

                                        <input type="hidden" name="details[{{ $idx }}][diagnosis_code]" value="{{ $diag->WC_RDIAG_CODE }}">

                                        <div class="row g-3">
                                            <!-- Dressing -->
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-bold fs-8 text-secondary mb-1">الضمادة المطبقة اليوم</label>
                                                <select name="details[{{ $idx }}][dressing_code]" class="form-select fs-7 bg-white">
                                                    <option value="">اختر الضمادة (اختياري)...</option>
                                                    @foreach($dressings as $dr)
                                                        <option value="{{ $dr->WC_DRESS_CODE }}">{{ $dr->WC_DRESS_NAME }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Medication -->
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-bold fs-8 text-secondary mb-1">العلاج الموضعي اليوم</label>
                                                <select name="details[{{ $idx }}][medication_code]" class="form-select fs-7 bg-white">
                                                    <option value="">اختر العلاج الموضعي (اختياري)...</option>
                                                    @foreach($medications as $med)
                                                        <option value="{{ $med->WC_MED_CODE }}">{{ $med->WC_MED_NAME }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Per-wound Outcome -->
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-bold fs-8 text-secondary mb-1">حالة هذا الجرح اليوم <span class="text-danger">*</span></label>
                                                <select name="details[{{ $idx }}][outcome_status]" class="form-select fs-7 bg-white fw-semibold" required>
                                                    <option value="IMPROVED_FOLLOWUP" selected>تحسن - مستمر في المتابعة</option>
                                                    <option value="IMPROVED_DISCHARGE">مكتمل الشفاء والالتئام</option>
                                                    <option value="WORSENED">تراجع / زيادة إفرازات</option>
                                                    <option value="DECEASED">تلف أنسجة شديد</option>
                                                </select>
                                            </div>

                                            <!-- Observation -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold fs-8 text-secondary mb-1">الملاحظة السريرية للجرح</label>
                                                <input type="text" name="details[{{ $idx }}][observation]" class="form-control fs-7 bg-white" placeholder="أدخل أي ملاحظات حول حجم الجرح، إفرازاته، أو الأنسجة...">
                                            </div>

                                            <!-- Wound Photos -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold fs-8 text-secondary mb-1"><i class="bi bi-camera-fill me-1 text-primary"></i>صورة متابعة مراجعة الجرح (اختياري — يمكن إرفاق صور متعددة)</label>
                                                <input type="file" name="details[{{ $idx }}][wound_photos][]" class="form-control fs-7 bg-white" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: GENERAL VISIT NOTES -->
                    <div class="card border-0 shadow-xs rounded-3 bg-white">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold text-dark fs-7 mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-journal-text text-primary fs-5"></i>
                                <span>الملاحظات السريرية العامة للزيارة</span>
                            </label>
                            <textarea name="general_notes" class="form-control fs-7" rows="2" placeholder="أدخل أي ملاحظات عامة حول صحة المريض والتزامه بالتعليمات السريرية..."></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-white px-4 py-3 border-top">
                    <button type="button" class="btn btn-light px-4 rounded-2" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-2 shadow-sm fw-bold">
                        <i class="bi bi-check-lg me-1"></i>حفظ التقييم والزيارة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .outcome-card {
        transition: all 0.2s ease-in-out;
    }
    .outcome-card:hover {
        border-color: var(--bs-primary) !important;
        background-color: var(--bs-primary-bg-subtle) !important;
    }
    .outcome-card:has(input:checked) {
        border-color: var(--bs-primary) !important;
        background-color: rgba(13, 110, 253, 0.04) !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="overall_status"]');
        const scheduledContainer = document.getElementById('scheduledDateContainer');

        function toggleScheduledDate() {
            const selected = document.querySelector('input[name="overall_status"]:checked');
            if (selected && selected.value === 'IMPROVED_SCHEDULED') {
                scheduledContainer.classList.remove('d-none');
            } else {
                scheduledContainer.classList.add('d-none');
            }
        }

        radios.forEach(radio => radio.addEventListener('change', toggleScheduledDate));
        toggleScheduledDate();
    });
</script>

