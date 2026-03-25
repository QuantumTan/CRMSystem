@php $openDelete = (bool) $deleting; @endphp

<div class="modal fade {{ $openDelete ? 'show' : '' }}" id="deleteModal" tabindex="-1"
    style="{{ $openDelete ? 'display:block;' : '' }}" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-0 pb-0">
                <a href="{{ route('customers.index') }}" class="btn-close ms-auto"></a>
            </div>

            <div class="modal-body pt-2 text-center px-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3.5rem;"></i>
                <h4 class="mt-3 mb-2">Delete Customer</h4>
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong>{{ $deleting?->first_name }} {{ $deleting?->last_name }}</strong>?
                </p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>

            <div class="modal-footer flex-column flex-sm-row justify-content-center border-0 pb-4 gap-2">
                <a href="{{ route('customers.index') }}" class="btn btn-light w-100 w-sm-auto">Cancel</a>
                @if ($deleting)
                    <form method="POST" action="{{ route('customers.destroy', $deleting->id) }}"
                        class="m-0 w-100 w-sm-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Yes, Delete
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>

@if ($openDelete)
    <div class="modal-backdrop fade show"></div>
    <style>
        body {
            overflow: hidden;
            padding-right: 0 !important;
        }
    </style>
@endif
