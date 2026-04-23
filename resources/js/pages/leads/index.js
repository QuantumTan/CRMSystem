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

    initLeadFilters(root);
}
