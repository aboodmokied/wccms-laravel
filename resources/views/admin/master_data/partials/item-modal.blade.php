{{-- Modal for Creating New Master Data Item --}}
<div class="modal fade" id="createItemModal" tabindex="-1" aria-labelledby="createItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createItemModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>إضافة {{ $domainTitle }} جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form action="{{ $storeRoute }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name_create" class="form-label fw-bold">الاسم باللغة العربية <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name_create" name="name" placeholder="أدخل اسم العنصر المرجعي..." required max="255">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modals for Editing Master Data Items --}}
@foreach($items as $item)
    @php
        $itemCode = $item->{$codeField};
        $itemName = $item->{$nameField};
        $updateUrl = route($updateRouteName, $itemCode);
    @endphp
    <div class="modal fade" id="editItemModal-{{ $itemCode }}" tabindex="-1" aria-labelledby="editItemModalLabel-{{ $itemCode }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editItemModalLabel-{{ $itemCode }}">
                        <i class="bi bi-pencil-square me-2"></i>تعديل {{ $domainTitle }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form action="{{ $updateUrl }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name_edit_{{ $itemCode }}" class="form-label fw-bold">الاسم باللغة العربية <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_edit_{{ $itemCode }}" name="name" value="{{ $itemName }}" required max="255">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>تحديث
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
