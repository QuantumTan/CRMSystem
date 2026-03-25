@props(['status' => null])

@php
    $normalizedStatus = strtolower((string) $status);

    $badgeClasses = match ($normalizedStatus) {
        'active' => 'badge bg-success-subtle text-success-emphasis border-0 border-success-subtle',
        'inactive' => 'badge bg-secondary-subtle text-secondary-emphasis border-0 border-secondary-subtle',
        default => 'badge bg-light text-dark border',
    };
@endphp

<span {{ $attributes->merge(['class' => $badgeClasses]) }}>
    {{ ucfirst($normalizedStatus ?: 'unknown') }}
</span>
