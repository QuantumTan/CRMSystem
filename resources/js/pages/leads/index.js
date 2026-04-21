function showNotification(message, type = 'info') {
    const alert = document.createElement('div');

    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed shadow`;
    alert.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;';
    alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;

    document.body.appendChild(alert);

    window.setTimeout(function () {
        alert.remove();
    }, 4000);
}

function initLeadDeletion(root, leadsBaseUrl, csrfToken) {
    root.querySelectorAll('[data-delete-lead]').forEach(function (button) {
        button.addEventListener('click', function () {
            const leadId = button.dataset.deleteLead;

            if (!leadId) {
                return;
            }

            if (!window.confirm('Are you sure you want to delete this lead? This cannot be undone.')) {
                return;
            }

            fetch(`${leadsBaseUrl}/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        showNotification(data.message ?? 'Error deleting lead.', 'danger');
                        return;
                    }

                    root.querySelector(`tr[data-lead-id="${leadId}"]`)?.remove();
                    showNotification('Lead deleted successfully!', 'success');
                })
                .catch(function () {
                    showNotification('Network error. Please try again.', 'danger');
                });
        });
    });
}

function initLeadFilters(root) {
    const searchInput = root.querySelector('[data-lead-filter="search"]');
    const statusSelect = root.querySelector('[data-lead-filter="status"]');
    const prioritySelect = root.querySelector('[data-lead-filter="priority"]');
    const assignedSelect = root.querySelector('[data-lead-filter="assigned-user"]');

    function filterLeads() {
        const searchTerm = (searchInput?.value || '').toLowerCase();
        const statusFilter = statusSelect?.value || '';
        const priorityFilter = prioritySelect?.value || '';
        const assignedFilter = assignedSelect?.value || '';

        root.querySelectorAll('tbody tr[data-lead-id]').forEach(function (row) {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status ?? '';
            const rowPriority = row.dataset.priority ?? '';
            const rowAssigned = row.dataset.assigned ?? '';

            const matchSearch = !searchTerm || text.includes(searchTerm);
            const matchStatus = !statusFilter || rowStatus === statusFilter;
            const matchPriority = !priorityFilter || rowPriority === priorityFilter;
            const matchAssigned = !assignedFilter || rowAssigned === assignedFilter;

            row.style.display = matchSearch && matchStatus && matchPriority && matchAssigned ? '' : 'none';
        });
    }

    [searchInput, statusSelect, prioritySelect, assignedSelect].forEach(function (field) {
        if (!field) {
            return;
        }

        const eventName = field.tagName === 'INPUT' ? 'keyup' : 'change';
        field.addEventListener(eventName, filterLeads);
    });

    if (searchInput?.value || statusSelect?.value || prioritySelect?.value || assignedSelect?.value) {
        filterLeads();
    }
}

export function initLeadIndexPage() {
    const root = document.querySelector('[data-lead-index-page]');

    if (!root) {
        return;
    }

    const leadsBaseUrl = root.dataset.leadsBaseUrl;
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (!leadsBaseUrl || !csrfToken) {
        return;
    }

    initLeadDeletion(root, leadsBaseUrl, csrfToken);
    initLeadFilters(root);
}
