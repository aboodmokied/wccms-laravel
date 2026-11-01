<div class="modal fade" id="rescheduleModal-{{ $item->WC_SREV_CODE }}" tabindex="-1" aria-labelledby="rescheduleModalLabel-{{ $item->WC_SREV_CODE }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="rescheduleModalLabel-{{ $item->WC_SREV_CODE }}">
                    <i class="bi bi-calendar-event me-2"></i>إعادة جدولة موعد المراجعة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('reviews.scheduled.reschedule', $item->WC_SREV_CODE) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">اسم المريض</label>
                        <div class="fw-bold text-dark fs-6">{{ $item->registration?->patient?->full_name ?? 'غير محدد' }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduled_date_{{ $item->WC_SREV_CODE }}" class="form-label fw-bold">تاريخ ووقت الموعد الجديد <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="scheduled_date_{{ $item->WC_SREV_CODE }}" name="scheduled_date" value="{{ \Illuminate\Support\Carbon::parse($item->WC_SREV_SCHEDULED_DATE)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes_{{ $item->WC_SREV_CODE }}" class="form-label fw-bold">ملاحظات أو سبب الجدولة</label>
                        <textarea class="form-control" id="notes_{{ $item->WC_SREV_CODE }}" name="notes" rows="3" placeholder="أدخل أي ملاحظات إضافية (اختياري)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>حفظ الموعد الجديد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
