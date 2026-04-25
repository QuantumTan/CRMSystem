@props(['status' => null])

@php
    $normalizedStatus = strtolower((string) $status);

    $badgeClasses = match ($normalizedStatus) {
        'active' => 'crm-table-status crm-table-status-success',
        'inactive' => 'crm-table-status crm-table-status-muted',
        default => 'crm-table-status crm-table-status-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => $badgeClasses]) }}>
    {{ ucfirst($normalizedStatus ?: 'unknown') }}
</span>
