@extends('layouts.app')

@section('title', 'تفاصيل طلب الإحالة وتقييم زيارة القسم')

@section('content')
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <x-card title="تفاصيل طلب الإحالة وتقييم الحالة" icon="bi-file-earmark-medical-fill" subtitle="معاينة بيانات المريض وإجراء زيارة التقييم بالقسم الإيوائي">

                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <span class="text-muted small d-block">كود الطلب</span>
                        <h4 class="fw-bold text-primary mb-0">REQ-{{ substr($request->WC_REQ_CODE, 0, 8) }}</h4>
                    </div>
                    <div>
                        <x-status-badge :status="$request->WC_REQ_STATUS" class="fs-6" />
                    </div>
                </div>

                <!-- Patient & Referral Info Summary -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-2"></i>بيانات المريض المحول</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">اسم المريض:</td>
                                        <td class="fw-bold">{{ $request->WC_REQ_PAT_NAME_SNAPSHOT }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">رقم الهوية:</td>
                                        <td><code>{{ $request->WC_REQ_PAT_ID_SNAPSHOT }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">رقم الهاتف:</td>
                                        <td>{{ $request->WC_REQ_PAT_PHONE_SNAPSHOT ?? 'غير مدخل' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">تاريخ الطلب:</td>
                                        <td>{{ \Carbon\Carbon::parse($request->WC_REQ_CREATED_AT)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-hospital-fill me-2"></i>معلومات القسم والمستفيدين</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 150px;">الممرض المحول:</td>
                                        <td class="fw-bold">{{ $request->nurse?->PER_EMP_FULL_NAME ?? 'غير محدد' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">مدخل البيانات:</td>
                                        <td>{{ $request->entryUser?->PER_USER_USERNAME ?? 'النظام' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">ممرض عيادة الجروح:</td>
                                        <td class="fw-bold text-primary">{{ $request->reviewer?->PER_EMP_FULL_NAME ?? 'قيد التعيين عند بدء الزيارة' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="card bg-white border mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-chat-left-text-fill me-2 text-primary"></i>سبب الإحالة والملاحظات السريرية
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $request->WC_REQ_NOTES ?: 'لا توجد ملاحظات إضافية.' }}</p>
                    </div>
                </div>

                <!-- Workflow Actions & Assessment Form for Clinic Nurses -->
                @if (in_array($request->WC_REQ_STATUS, ['PENDING', 'VISIT_IN_PROGRESS']))
                    <div class="card border-primary shadow-sm mb-4">
                        <div class="card-header bg-primary text-white py-3 fw-bold d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 fs-6">
                                <i class="bi bi-journal-medical fs-5"></i>
                                <span>تقييم حالة الجرح وتوثيق البيانات والتشخيصات (Diagnoses)</span>
                            </div>
                            <span class="badge bg-white text-primary">إدخال مباشر بالصفحة</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('requests.accept', $request->WC_REQ_CODE) }}" method="POST" id="acceptRequestForm" enctype="multipart/form-data">
                                @csrf

                                <div class="alert alert-info border-0 shadow-sm mb-4">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    يرجى تحديد الممرض الذي أجرى الفحص، وقسم العيادة المستقبل، وتوثيق التشخيصات والجرح مباشرة. يمكنك إضافة أكثر من تشخيص للجرح وإرفاق صور مبدئية للجرح.
                                </div>

                                <!-- Section 1: Staff & Clinic Info -->
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-badge-fill me-2"></i>معلومات التقييم السريري والممرض الفاحص
                                </h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="clinic_nurse_code" class="form-label fw-bold text-dark">
                                            الممرض الذي أجرى الفحص والتقييم <span class="text-danger">*</span>
                                        </label>
                                        <select name="clinic_nurse_code" id="clinic_nurse_code" class="form-select form-select-lg border-primary-subtle" required>
                                            <option value="">-- اختر الممرض الفاحص --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->PER_EMP_CODE }}" {{ ($request->WC_REQ_REVIEWED_BY_CD == $emp->PER_EMP_CODE) ? 'selected' : '' }}>
                                                    {{ $emp->PER_EMP_FULL_NAME }} ({{ $emp->PER_EMP_JOB_TITLE ?? 'ممرض' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="department_code" class="form-label fw-bold text-dark">
                                            قسم العيادة المستقبِل <span class="text-danger">*</span>
                                        </label>
                                        <select name="department_code" id="department_code" class="form-select form-select-lg border-primary-subtle" required>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->WC_DEPT_CODE }}" {{ (old('department_code') == $dept->WC_DEPT_CODE || (!old('department_code') && \Illuminate\Support\Str::contains($dept->WC_DEPT_NAME, ['Wound Care', 'جروح']))) ? 'selected' : '' }}>
                                                    {{ $dept->WC_DEPT_NAME }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="procedure_code" class="form-label fw-semibold">
                                            الإجراء الطبي الأول المتبع (اختياري)
                                        </label>
                                        <select name="procedure_code" id="procedure_code" class="form-select">
                                            <option value="">-- بدون تحديد إجراء طبي أول --</option>
                                            @foreach ($procedures as $proc)
                                                <option value="{{ $proc->WC_PROC_CODE }}">{{ $proc->WC_PROC_NAME }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="pmh_notes" class="form-label fw-semibold">
                                            التاريخ المرضي والملاحظات السريرية للفحص
                                        </label>
                                        <textarea name="pmh_notes" id="pmh_notes" rows="3" class="form-control" placeholder="أدخل التاريخ المرضي السابق للمريض وملاحظات فحص الجرح السريري..."></textarea>
                                    </div>
                                </div>

                                <!-- Section 2: Multiple Diagnoses Support -->
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                            <h6 class="fw-bold text-primary mb-0">
                                                <i class="bi bi-clipboard2-pulse-fill me-2"></i>
                                                تشخيصات الجرح (Diagnoses) — إمكانية إضافة عدة تشخيصات
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addDiagnosisRowBtn">
                                                <i class="bi bi-plus-circle-fill me-1"></i> إضافة تشخيص آخر
                                            </button>
                                        </div>

                                        <div id="diagnosesContainer">
                                            <!-- Row 0 -->
                                            <div class="row g-2 align-items-end diagnosis-row mb-3 pb-3 border-bottom border-secondary-subtle">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small text-muted">نوع الجرح (Wound Type)</label>
                                                    <select name="diagnoses[0][wound_type_code]" class="form-select">
                                                        <option value="">-- اختر نوع الجرح --</option>
                                                        @foreach ($woundTypes as $wt)
                                                            <option value="{{ $wt->WC_WTYPE_CODE }}">{{ $wt->WC_WTYPE_NAME }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small text-muted">التشخيص الطبي للجرح (Diagnosis)</label>
                                                    <select name="diagnoses[0][diagnosis_code]" class="form-select">
                                                        <option value="">-- اختر التشخيص الطبي --</option>
                                                        @foreach ($diagnoses as $diag)
                                                            <option value="{{ $diag->WC_DIAG_CODE }}">{{ $diag->WC_DIAG_NAME }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small text-muted">العلاج الدوائي (Medication)</label>
                                                    <select name="diagnoses[0][medication_code]" class="form-select">
                                                        <option value="">-- اختر العلاج الدوائي --</option>
                                                        @foreach ($medications as $med)
                                                            <option value="{{ $med->WC_MED_CODE }}">{{ $med->WC_MED_NAME }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-diagnosis-btn" disabled title="حذف التشخيص">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="col-md-12 mt-2">
                                                    <label class="form-label fw-semibold small text-muted"><i class="bi bi-camera-fill me-1 text-primary"></i>صورة مبدئية للجرح (اختياري — يمكن اختيار صور متعددة)</label>
                                                    <input type="file" name="diagnoses[0][wound_photos][]" class="form-control form-control-sm" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow-sm">
                                        <i class="bi bi-check-circle-fill me-2"></i> قبول الطلب وإنشاء الملف الطبي مع التشخيصات
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-lg px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#ineligibleModal">
                                        <i class="bi bi-x-circle-fill me-2"></i> عدم الأهلية للتحويل
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card border-secondary mb-4">
                        <div class="card-body">
                            <div class="alert alert-secondary mb-0">
                                <i class="bi bi-lock-fill me-2"></i>تم البت في هذا الطلب سابقاً وحالته الحالية هي: <strong><x-status-badge :status="$request->WC_REQ_STATUS" /></strong>.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-start mb-4">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i> العودة لقائمة الطلبات
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>

<!-- Modal for Ineligible Reason -->
<div class="modal fade" id="ineligibleModal" tabindex="-1" aria-labelledby="ineligibleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('requests.ineligible', $request->WC_REQ_CODE) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="ineligibleModalLabel"><i class="bi bi-x-circle-fill me-2"></i>تسجيل عدم الأهلية للتحويل</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">سبب عدم الأهلية للعيادة <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" placeholder="يرجى كتابة التقييم السريري وسبب عدم الأهلية بالتفصيل..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد عدم الأهلية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('diagnosesContainer');
    const addBtn = document.getElementById('addDiagnosisRowBtn');
    let rowCount = 1;

    if (addBtn && container) {
        addBtn.addEventListener('click', function () {
            const firstRow = container.querySelector('.diagnosis-row');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('select').forEach(select => {
                select.value = '';
                const name = select.getAttribute('name');
                if (name) {
                    select.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
                }
            });

            const removeBtn = newRow.querySelector('.remove-diagnosis-btn');
            if (removeBtn) {
                removeBtn.removeAttribute('disabled');
                removeBtn.addEventListener('click', function () {
                    newRow.remove();
                });
            }

            container.appendChild(newRow);
            rowCount++;
        });
    }
});
</script>
@endpush
