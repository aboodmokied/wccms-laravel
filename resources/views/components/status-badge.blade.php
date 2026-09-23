@props(['status' => 'PENDING', 'label' => null])

@php
    $classMap = [
        'PENDING' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'VISIT_IN_PROGRESS' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'ACCEPTED' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'APPROVED' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'NOT_ELIGIBLE' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'REJECTED' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'CANCELLED' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'ACTIVE' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
        'DISCHARGED' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'COMPLETED' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'VOIDED' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
    ];

    $labelMap = [
        'PENDING' => 'قيد الانتظار',
        'VISIT_IN_PROGRESS' => 'زيارة قيد التنفيذ',
        'ACCEPTED' => 'مقبول (مسجل)',
        'APPROVED' => 'مقبول',
        'NOT_ELIGIBLE' => 'غير مؤهل',
        'REJECTED' => 'مرفوض',
        'CANCELLED' => 'ملغى',
        'ACTIVE' => 'نشط',
        'DISCHARGED' => 'خرج',
        'COMPLETED' => 'مكتمل',
        'VOIDED' => 'ملغى تلقائياً',
    ];

    $badgeClass = $classMap[strtoupper($status)] ?? 'bg-light text-dark border-light-subtle';
    $displayText = $label ?? ($labelMap[strtoupper($status)] ?? $status);
@endphp

<span {{ $attributes->merge(['class' => 'badge border px-2.5 py-1.5 rounded-pill font-semibold ' . $badgeClass]) }}>
    {{ $displayText }}
</span>
