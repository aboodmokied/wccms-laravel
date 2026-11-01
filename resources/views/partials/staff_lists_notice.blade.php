{{-- Shown when a pick list could not be filtered by job (its C_JOB_TB codes are unknown): never silently. --}}
@if (! empty($unfilteredRoles))
    <div class="col-12">
        <div class="alert alert-warning py-2 small mb-3" role="status">
            قوائم {{ implode(' و', $unfilteredRoles) }} تعرض كل الموظفين النشطين، لأن أكواد وظائفها في C_JOB_TB لم تُحدَّد.
            تأكد من اختيار الموظف الصحيح، وأبلغ مسؤول النظام (wccms:check-hospital).
        </div>
    </div>
@endif
