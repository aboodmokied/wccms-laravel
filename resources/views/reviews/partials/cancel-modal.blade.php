<div class="modal fade" id="cancelModal-{{ $item->WC_SREV_CODE }}" tabindex="-1" aria-labelledby="cancelModalLabel-{{ $item->WC_SREV_CODE }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelModalLabel-{{ $item->WC_SREV_CODE }}">
                    <i class="bi bi-x-circle me-2"></i>إلغاء موعد المراجعة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ route('reviews.scheduled.cancel', $item->WC_SREV_CODE) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning border-0 d-flex align-items-center mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>هل أنت تأكد من رغبتك في إلغاء هذا الموعد المجدول؟</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">اسم المريض</label>
                        <div class="fw-bold text-dark fs-6">{{ $item->registration?->patient?->MR_PAT_FULL_NAME ?? 'غير محدد' }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="cancel_notes_{{ $item->WC_SREV_CODE }}" class="form-label fw-bold">سبب الإلغاء</label>
                        <textarea class="form-control" id="cancel_notes_{{ $item->WC_SREV_CODE }}" name="notes" rows="3" placeholder="أدخل سبب إلغاء الموعد (مثال: اعتذار المريض، عدم الحضور)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-x-lg me-1"></i>تأكيد إلغاء الموعد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
