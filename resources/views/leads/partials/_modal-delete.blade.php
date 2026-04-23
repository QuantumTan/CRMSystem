<div class="modal fade" id="deleteLeadModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2 text-center px-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3.5rem;"></i>
                <h4 class="mt-3 mb-2">Delete Lead</h4>
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong data-delete-modal-name></strong>?
                </p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>

            <div class="modal-footer flex-column flex-sm-row justify-content-center border-0 pb-4 gap-2">
                <button type="button" class="btn btn-light w-100 w-sm-auto" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="m-0 w-100 w-sm-auto" data-delete-modal-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
