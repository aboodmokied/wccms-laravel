@props(['status' => 'PENDING', 'label' => null])

@php
    $classMap = [
        'PENDING' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'APPROVED' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'REJECTED' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'CANCELLED' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'ACTIVE' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
        'DISCHARGED' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'COMPLETED' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'VOIDED' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
    ];

    $badgeClass = $classMap[strtoupper($status)] ?? 'bg-light text-dark border-light-subtle';
    $displayText = $label ?? __('status.' . strtolower($status));
@endphp

<span {{ $attributes->merge(['class' => 'badge border px-2.5 py-1.5 rounded-pill font-semibold ' . $badgeClass]) }}>
    {{ $displayText }}
</span>
