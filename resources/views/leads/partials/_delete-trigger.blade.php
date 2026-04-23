@php
    $buttonClass = $buttonClass ?? 'btn btn-outline-danger d-inline-flex align-items-center gap-2';
    $buttonLabel = $buttonLabel ?? 'Delete Lead';
@endphp

@if (! $lead->isConverted())
    @can('delete', $lead)
        <button type="button" class="{{ $buttonClass }}" data-delete-modal-trigger
            data-delete-modal-target="#deleteLeadModal"
            data-delete-action="{{ route('leads.destroy', $lead) }}"
            data-delete-name="{{ $lead->name }}">

            {{ $buttonLabel }}
        </button>
    @endcan
@endif
