<!-- Modal: Discharge Patient -->
<div class="modal fade" id="dischargeModal" tabindex="-1" aria-labelledby="dischargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="dischargeModalLabel">
                    <i class="bi bi-box-arrow-right me-2"></i>تأكيد تخريج المريض من عيادة العناية بالجروح
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('registrations.discharge', $registration->WC_REG_CODE) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
                        <div>
                            <strong>تنبيه:</strong> سيتم تحويل حالة هذا التسجيل إلى <strong>مُخرج (Discharged)</strong> وتجميد إضافة تشخيصات جروح جديدة.
                        </div>
                    </div>

                    @include('partials.staff_lists_notice')
                    <div class="mb-3">
                        <label class="form-label fw-bold">الممرض المنفذ للتخريج <span class="text-danger">*</span></label>
                        <select name="discharging_nurse_code" class="form-select" required>
                            <option value="">اختر الممرض المنفذ للتخريج...</option>
                            @foreach($nurses as $emp)
                                <option value="{{ $emp->EMP_SERIAL }}" @selected((string) old('discharging_nurse_code') === (string) $emp->EMP_SERIAL)>
                                    {{ $emp->full_name }} ({{ $emp->job_title ?? 'موظف عيادة' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-check-circle me-1"></i>تأكيد التخريج
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
