<div class="table-responsive">
    <table class="table table-hover align-middle border-top">
        <thead class="table-light">
            <tr>
                <th style="width: 15%;">كود العنصر</th>
                <th style="width: 45%;">الاسم باللغة العربية</th>
                <th style="width: 20%;">الحالة</th>
                <th style="width: 20%;" class="text-end">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $itemCode = $item->{$codeField};
                    $itemName = $item->{$nameField};
                    $isActive = (int) $item->{$activeField} === 1;
                    $toggleUrl = route($toggleRouteName, $itemCode);
                @endphp
                <tr>
                    <td class="fw-bold text-secondary">
                        <small>{{ Str::limit($itemCode, 10, '') }}</small>
                    </td>
                    <td class="fw-bold text-dark fs-6">
                        {{ $itemName }}
                    </td>
                    <td>
                        @if($isActive)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i>نشط
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                                <i class="bi bi-x-circle-fill me-1"></i>غير نشط
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            {{-- Edit Button Trigger --}}
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editItemModal-{{ $itemCode }}" title="تعديل الاسم">
                                <i class="bi bi-pencil-square me-1"></i>تعديل
                            </button>

                            {{-- Toggle Status Form --}}
                            <form action="{{ $toggleUrl }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                @if($isActive)
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="تعطيل العنصر">
                                        <i class="bi bi-slash-circle me-1"></i>تعطيل
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="تفعيل العنصر">
                                        <i class="bi bi-check2-circle me-1"></i>تفعيل
                                    </button>
                                @endif
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        لا توجد عناصر مرجعية مسجلة في هذا القسم.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($items->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            عرض {{ $items->firstItem() ?? 0 }} إلى {{ $items->lastItem() ?? 0 }} من أصل {{ $items->total() }} عنصر
        </div>
        <div>
            {{ $items->links() }}
        </div>
    </div>
@endif
