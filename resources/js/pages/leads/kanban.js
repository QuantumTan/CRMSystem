function highlightTargetCard(root) {
    if (!window.location.hash.startsWith('#lead-kanban-card-')) {
        return;
    }

    const targetCard = root.querySelector(window.location.hash);

    if (!targetCard) {
        return;
    }

    targetCard.setAttribute('tabindex', '-1');

    targetCard.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center',
    });

    targetCard.classList.add('kanban-card-targeted');
    targetCard.focus({
        preventScroll: true,
    });

    window.setTimeout(function () {
        targetCard.classList.remove('kanban-card-targeted');
    }, 2500);
}

function submitLeadStatusUpdate(leadsBaseUrl, csrfToken, leadId, newStatus) {
    const form = document.createElement('form');
    const csrfInput = document.createElement('input');
    const methodInput = document.createElement('input');
    const statusInput = document.createElement('input');

    form.method = 'POST';
    form.action = `${leadsBaseUrl}/kanban/${leadId}/status`;

    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;

    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';

    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = newStatus;

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

function initDragAndDrop(root, leadsBaseUrl, csrfToken, canDragLeads) {
    const sortable = window.Sortable;

    root.querySelectorAll('.draggable-card').forEach(function (card) {
        if (canDragLeads) {
            return;
        }

        card.setAttribute('draggable', 'false');
        card.style.cursor = 'default';
    });

    if (!canDragLeads || !sortable) {
        return;
    }

    root.querySelectorAll('.droppable-zone').forEach(function (zone) {
        sortable.create(zone, {
            group: 'leads',
            animation: 150,
            ghostClass: 'card-ghost',
            dragClass: 'card-dragging',
            delay: 100,
            delayOnTouchOnly: true,
            onEnd(event) {
                const leadId = event.item.dataset.leadId;
                const newStatus = event.to.dataset.status;
                const oldStatus = event.from.dataset.status;

                if (!leadId || !newStatus || oldStatus === newStatus) {
                    return;
                }

                if (newStatus === 'lost') {
                    window.location.href = `${leadsBaseUrl}/${leadId}/lost-form`;
                    return;
                }

                submitLeadStatusUpdate(leadsBaseUrl, csrfToken, leadId, newStatus);
            },
        });
    });
}

export function initLeadKanbanPage() {
    const root = document.querySelector('[data-lead-kanban-page]');

    if (!root) {
        return;
    }

    const leadsBaseUrl = root.dataset.leadsBaseUrl;
    const canDragLeads = root.dataset.canDragLeads === 'true';
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (!leadsBaseUrl || !csrfToken) {
        return;
    }

    initDragAndDrop(root, leadsBaseUrl, csrfToken, canDragLeads);

    if (document.readyState === 'complete') {
        highlightTargetCard(root);
        return;
    }

    window.addEventListener(
        'load',
        function () {
            highlightTargetCard(root);
        },
        { once: true }
    );
}
