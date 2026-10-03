<!-- Modal: Add Wound Diagnosis -->
<div class="modal fade" id="addWoundModal" tabindex="-1" aria-labelledby="addWoundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addWoundModalLabel">
                    <i class="bi bi-bandaid me-2"></i>إضافة تشخيص جرح جديد وتحديد العلاج الأولي
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('registrations.diagnoses.store', $registration->WC_REG_CODE) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Wound Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">نوع الجرح <span class="text-danger">*</span></label>
                            <select name="wound_type_code" class="form-select" required>
                                <option value="">اختر نوع الجرح...</option>
                                @foreach($woundTypes as $wt)
                                    <option value="{{ $wt->WC_WTYPE_CODE }}">{{ $wt->WC_WTYPE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Diagnosis -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">التشخيص السريري <span class="text-danger">*</span></label>
                            <select name="diagnosis_code" class="form-select" required>
                                <option value="">اختر التشخيص السريري...</option>
                                @foreach($diagnoses as $diag)
                                    <option value="{{ $diag->WC_DIAG_CODE }}">{{ $diag->WC_DIAG_NAME }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dressing -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">الضمادة الأولية المقترحة</label>
                            <select name="dressing_code" class="form-select">
                                <option value="">اختر الضمادة الأولية (اختياري)...</option>
                                @foreach($dressings as $dr)
                                    <option value="{{ $dr->WC_DRESS_CODE }}">{{ $dr->WC_DRESS_NAME }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Medication -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">العلاج الموضعي الأولي</label>
                            <select name="medication_code" class="form-select">
                                <option value="">اختر العلاج الموضعي (اختياري)...</option>
                                @foreach($medications as $med)
                                    <option value="{{ $med->WC_MED_CODE }}">{{ $med->WC_MED_NAME }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Examining Nurse -->
                        <div class="col-12">
                            <label class="form-label fw-bold">الممرض الفاحص / السريري <span class="text-danger">*</span></label>
                            <select name="clinical_nurse_code" class="form-select" required>
                                <option value="">اختر الممرض الفاحص...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->PER_EMP_CODE }}" {{ (Auth::user()->employee?->PER_EMP_CODE === $emp->PER_EMP_CODE) ? 'selected' : '' }}>
                                        {{ $emp->PER_EMP_FULL_NAME }} ({{ $emp->PER_EMP_JOB_TITLE ?? 'موظف عيادة' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">يتم تسجيل اسم المستخدم الحالي تلقائياً كمُدخل للبيانات (Data-Entry Audit).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>حفظ التشخيص
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
