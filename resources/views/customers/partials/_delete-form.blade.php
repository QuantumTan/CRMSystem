@php
    $buttonClass = $buttonClass ?? 'btn btn-outline-danger d-inline-flex align-items-center gap-2';
    $buttonLabel = $buttonLabel ?? 'Delete Customer';
@endphp

@can('delete', $customer)
    <button type="button" class="{{ $buttonClass }}" data-delete-modal-trigger
        data-delete-modal-target="#deleteModal"
        data-delete-action="{{ route('customers.destroy', $customer) }}"
        data-delete-name="{{ trim($customer->first_name.' '.$customer->last_name) }}">
        {{ $buttonLabel }}
    </button>
@endcan
