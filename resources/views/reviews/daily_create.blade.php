@extends('layouts.app')

@section('title', 'إضافة تقييم يومي للجروح - WCCMS')

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE HEADER BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('registrations.daily-reviews.index', $registration->WC_REG_CODE) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs rounded-3">
                <i class="bi bi-arrow-right"></i>
                <span>العودة للزيارات</span>
            </a>
            <div>
                <h1 class="fw-bold fs-4 mb-0 text-dark">
                    @if(isset($scheduledReview) && $scheduledReview)
                        <i class="bi bi-calendar-check me-1 text-primary"></i>توثيق الموعد المجدول (#{{ $scheduledReview->WC_SREV_VISIT_SERIAL }})
                    @else
                        توثيق التقييم والزيارة اليومية للجروح
                    @endif
                </h1>
                <span class="text-muted fs-7">تسجيل المريض رقم: <code class="text-primary fw-bold">{{ $registration->WC_REG_CODE }}</code> | تاريخ اليوم: {{ now()->format('Y-m-d') }}</span>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-2xs border-0 mb-4 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <form action="{{ route('registrations.daily-reviews.store', $registration->WC_REG_CODE) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($scheduledReview) && $scheduledReview)
            <input type="hidden" name="scheduled_review" value="{{ $scheduledReview->WC_SREV_CODE }}">
        @elseif(request('scheduled_review'))
            <input type="hidden" name="scheduled_review" value="{{ request('scheduled_review') }}">
        @endif

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <!-- SECTION 3: PER-WOUND EVALUATION (OPEN CLEAR CARDS) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark fs-6 mb-4 d-flex align-items-center justify-content-between border-bottom pb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-bandaid text-primary fs-4"></i>
                                <span>تقييم الجروح النشطة للمريض</span>
                            </div>
                            <span class="badge bg-primary text-white fs-8 rounded-pill">{{ $registration->diagnoses->count() }} جرح</span>
                        </h6>

                        <div class="d-flex flex-column gap-4">
                            @foreach($registration->diagnoses as $idx => $diag)
                                <div class="border rounded-4 p-4 bg-light-subtle position-relative shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fs-8 fw-bold">جرح #{{ $idx + 1 }}</span>
                                            <h5 class="fw-bold fs-6 mb-0 text-dark">
                                                {{ $diag->woundType?->WC_WTYPE_NAME }} — <span class="text-primary">{{ $diag->diagnosis?->WC_DIAG_NAME }}</span>
                                            </h5>
                                        </div>
                                    </div>

                                    <input type="hidden" name="details[{{ $idx }}][diagnosis_code]" value="{{ $diag->WC_RDIAG_CODE }}">

                                    <div class="row g-4">
                                        <!-- Dressing -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold fs-7 text-secondary mb-2">الضمادة المطبقة اليوم</label>
                                            <select name="details[{{ $idx }}][dressing_code]" class="form-select fs-7 bg-white">
                                                <option value="">اختر الضمادة (اختياري)...</option>
                                                @foreach($dressings as $dr)
                                                    <option value="{{ $dr->WC_DRESS_CODE }}">{{ $dr->WC_DRESS_NAME }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Medication -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold fs-7 text-secondary mb-2">العلاج الموضعي اليوم</label>
                                            <select name="details[{{ $idx }}][medication_code]" class="form-select fs-7 bg-white">
                                                <option value="">اختر العلاج الموضعي (اختياري)...</option>
                                                @foreach($medications as $med)
                                                    <option value="{{ $med->WC_MED_CODE }}">{{ $med->WC_MED_NAME }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Per-wound Outcome -->
                                        <div class="col-12 col-md-12">
                                            <label class="form-label fw-bold fs-7 text-secondary mb-2">حالة هذا الجرح اليوم <span class="text-danger">*</span></label>
                                            <select name="details[{{ $idx }}][outcome_status]" class="form-select fs-7 bg-white fw-semibold" required>
                                                <option value="IMPROVED_FOLLOWUP" selected>تحسن - مستمر في المتابعة</option>
                                                <option value="IMPROVED_DISCHARGE">مكتمل الشفاء والالتئام</option>
                                                <option value="WORSENED">تراجع / زيادة إفرازات</option>
                                                <option value="DECEASED">تلف أنسجة شديد</option>
                                            </select>
                                        </div>

                                        <!-- Observation -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold fs-7 text-secondary mb-2">الملاحظة السريرية للجرح</label>
                                            <textarea name="details[{{ $idx }}][observation]" class="form-control fs-7 bg-white" rows="2" placeholder="أدخل أي ملاحظات حول حجم الجرح، إفرازاته، أو الأنسجة..."></textarea>
                                        </div>

                                        <!-- Wound Photos -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold fs-7 text-secondary mb-2"><i class="bi bi-camera-fill me-1 text-primary"></i>صورة متابعة مراجعة الجرح (اختياري — يمكن إرفاق صور متعددة)</label>
                                            <input type="file" name="details[{{ $idx }}][wound_photos][]" class="form-control fs-7 bg-white" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: GENERAL VISIT NOTES -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <label class="form-label fw-bold text-dark fs-6 mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text text-primary fs-4"></i>
                            <span>الملاحظات السريرية العامة للزيارة</span>
                        </label>
                        <textarea name="general_notes" class="form-control fs-7" rows="4" placeholder="أدخل أي ملاحظات عامة حول صحة المريض والتزامه بالتعليمات السريرية..."></textarea>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <!-- SECTION 1: ATTENDING NURSE & DATA-ENTRY AUDIT -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark fs-6 mb-3 border-bottom pb-2">بيانات التوثيق</h6>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark fs-7 mb-2">
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
                        
                        <div class="p-3 bg-primary-subtle bg-opacity-30 rounded-3 border border-primary-subtle d-flex align-items-center gap-3">
                            <i class="bi bi-shield-check text-primary fs-3"></i>
                            <div>
                                <span class="d-block text-muted fs-8">مُدخل البيانات (Audit):</span>
                                <span class="fw-bold text-dark fs-6">{{ Auth::user()->PER_USER_USERNAME }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: OVERALL VISIT OUTCOME -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark fs-6 mb-3 border-bottom pb-2">النتيجة الإجمالية <span class="text-danger">*</span></h6>
                        
                        <div class="d-flex flex-column gap-3">
                            @if(isset($scheduledReview) && $scheduledReview)
                                <!-- SCHEDULED REVIEW OUTCOME CHOICES -->
                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-info-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_improved_scheduled" value="IMPROVED_SCHEDULED" checked>
                                        <div>
                                            <span class="fw-bold text-info d-block fs-7 mb-1">
                                                <i class="bi bi-calendar-check-fill text-info me-1"></i>تحسن وحجز مراجعة جديدة
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تحسن الحالة وحجز موعد مراجعة قادم للمتابعة.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-success-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_improved_discharge" value="IMPROVED_DISCHARGE">
                                        <div>
                                            <span class="fw-bold text-success d-block fs-7 mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>تحسن بدون مراجعة جديدة
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">اكتمل الشفاء والتئام الجروح بدون حاجة لمراجعة قادمة.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-warning-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_worsened_scheduled" value="WORSENED_SCHEDULED">
                                        <div>
                                            <span class="fw-bold text-warning-emphasis d-block fs-7 mb-1">
                                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>تدهور مع مراجعة جديدة
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تدهور أو زيادة إفرازات وحجز موعد مراجعة جديد.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-danger-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_worsened_admission" value="WORSENED_ADMISSION">
                                        <div>
                                            <span class="fw-bold text-danger d-block fs-7 mb-1">
                                                <i class="bi bi-hospital-fill text-danger me-1"></i>تدهور مع تسجيل للمستشفى
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تدهور حاد يستدعي تنويم وإدخال المريض للمستشفى.</small>
                                        </div>
                                    </div>
                                </label>
                            @else
                                <!-- DAILY REVIEW OUTCOME CHOICES -->
                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-success-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_improved_stay" value="IMPROVED_STAY" checked>
                                        <div>
                                            <span class="fw-bold text-success d-block fs-7 mb-1">
                                                <i class="bi bi-graph-up-arrow text-success me-1"></i>تحسن مع استمرار
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تحسن حالة الجرح مع استمرار المتابعة.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-warning-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_worsened_stay" value="WORSENED_STAY">
                                        <div>
                                            <span class="fw-bold text-warning-emphasis d-block fs-7 mb-1">
                                                <i class="bi bi-arrow-repeat text-warning me-1"></i>تدهور (مستمر بالعلاج)
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تدهور الجرح مع استمرار التعديل العلاجي.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-info-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_improved_scheduled" value="IMPROVED_SCHEDULED">
                                        <div>
                                            <span class="fw-bold text-info d-block fs-7 mb-1">
                                                <i class="bi bi-calendar-check-fill text-info me-1"></i>خروج مع مراجعة مجدولة
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">تخريج المريض وحجز موعد مراجعة مجدولة دائماً.</small>
                                        </div>
                                    </div>
                                </label>

                                <label class="outcome-card border rounded-3 p-3 bg-white d-block cursor-pointer transition-all border-secondary-subtle">
                                    <div class="form-check p-0 m-0 d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1 ms-2" type="radio" name="overall_status" id="overall_improved_discharge" value="IMPROVED_DISCHARGE">
                                        <div>
                                            <span class="fw-bold text-dark d-block fs-7 mb-1">
                                                <i class="bi bi-check-circle-fill text-dark me-1"></i>شفاء وتخريج نهائي
                                            </span>
                                            <small class="text-muted d-block fs-8 lh-sm">التئام كامل للجرح وإغلاق نهائي بدون مراجعة.</small>
                                        </div>
                                    </div>
                                </label>
                            @endif
                        </div>

                        <!-- Scheduled Date Picker (Shown when IMPROVED_SCHEDULED selected) -->
                        <div class="mt-4 p-3 bg-info-subtle border border-info-subtle rounded-3 d-none" id="scheduledDateContainer">
                            <label class="form-label fw-bold text-dark fs-7 mb-2">
                                <i class="bi bi-calendar-plus text-info me-1"></i>تاريخ موعد المراجعة القادم:
                            </label>
                            <input type="date" name="scheduled_date" class="form-control fs-7" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-3 rounded-4 shadow-sm fw-bolder fs-6">
                        <i class="bi bi-check-lg me-1"></i>حفظ التقييم والزيارة
                    </button>
                    <a href="{{ route('registrations.daily-reviews.index', $registration->WC_REG_CODE) }}" class="btn btn-light py-3 rounded-4 text-dark fw-bold">إلغاء</a>
                </div>
            </div>
        </div>
    </form>
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
            if (selected && (selected.value === 'IMPROVED_SCHEDULED' || selected.value === 'WORSENED_SCHEDULED')) {
                scheduledContainer.classList.remove('d-none');
            } else {
                scheduledContainer.classList.add('d-none');
            }
        }

        radios.forEach(radio => radio.addEventListener('change', toggleScheduledDate));
        toggleScheduledDate();
    });
</script>
@endsection
