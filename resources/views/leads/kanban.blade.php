@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-0 fw-semibold">Leads</h4>
                @php
                    $totalLeads = 0;
                    foreach ($leadsByStatus as $group) {
                        $totalLeads += count($group);
                    }
                @endphp
                <p class="text-muted mb-0 small">{{ $totalLeads }} total leads in pipeline</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leads.kanban') }}" 
                   class="btn btn-dark active d-flex align-items-center gap-2">
                    <i class="bi bi-kanban-fill"></i>
                    Kanban View
                </a>
                <a href="{{ route('leads.index') }}" 
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-table"></i>
                    Table View
                </a>

                <a href="{{ route('leads.create') }}" 
                   class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="bi bi-plus"></i>
                    Add Lead
                </a>
            </div>
        </div>

        {{-- FILTER TOOLKIT --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('leads.kanban') }}" method="GET"
                      class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                    {{-- Search --}}
                    <div class="position-relative" style="max-width: 300px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted small"></i>
                        <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                               value="{{ request('search') }}"
                               placeholder="Search name, email, phone...">
                    </div>

                    {{-- All Statuses --}}
                    <select id="status" name="status" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>

                    {{-- All Priorities --}}
                    <select id="priority" name="priority" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                        <option value="">All Priorities</option>
                        <option value="low"    @selected(request('priority') == 'low')>Low</option>
                        <option value="medium" @selected(request('priority') == 'medium')>Medium</option>
                        <option value="high"   @selected(request('priority') == 'high')>High</option>
                    </select>

                    {{-- All Users --}}
                    <select id="assigned_user" name="assigned_user" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('assigned_user') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('leads.kanban') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>

                </form>
            </div>
        </div>

        {{-- KANBAN BOARD --}}
        <div class="kanban-wrapper">
            @foreach ($statuses as $status)
                <div class="kanban-column">

                    {{-- Column Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-medium"
                                  style="background-color: {{ getStatusColor($status) }}; color: white; font-size: 0.9rem;">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary fw-normal status-count px-3 py-1"
                              data-status="{{ $status }}">
                            {{ count($leadsByStatus[$status]) }}
                        </span>
                    </div>

                    {{-- Droppable Zone --}}
                    <div class="kanban-cards droppable-zone" data-status="{{ $status }}">

                        @foreach ($leadsByStatus[$status] as $lead)
                            <div class="kanban-card card border-0 shadow-sm draggable-card" draggable="true"
                                 data-lead-id="{{ $lead->id }}" 
                                 data-assigned="{{ $lead->assigned_user_id ?? '' }}"
                                 data-status="{{ $status }}"
                                 data-priority="{{ strtolower($lead->priority) }}"
                                 style="border-left: 5px solid {{ getPriorityColor($lead->priority) }};">

                                <div class="card-body p-3">

                                    <h6 class="card-title mb-2 fw-semibold">
                                        <a href="{{ route('leads.show', $lead) }}" 
                                           class="text-decoration-none text-dark hover-underline">
                                            {{ $lead->name }}
                                        </a>
                                    </h6>

                                    <div class="small text-muted mb-3">
                                        @if ($lead->email)
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-envelope"></i>
                                                <a href="mailto:{{ $lead->email }}" class="text-muted text-decoration-none">
                                                    {{ Str::limit($lead->email, 28) }}
                                                </a>
                                            </div>
                                        @endif
                                        @if ($lead->phone)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-telephone"></i>
                                                <a href="tel:{{ $lead->phone }}" class="text-muted text-decoration-none">
                                                    {{ $lead->phone }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge px-2 py-1"
                                              style="background-color: {{ getPriorityColor($lead->priority) }}; color: white; font-size: 0.8rem;">
                                            {{ ucfirst($lead->priority) }}
                                        </span>

                                        @if ($lead->source)
                                            <span class="badge bg-light text-dark px-2 py-1">{{ $lead->source }}</span>
                                        @endif
                                    </div>

                                    @if ($lead->expected_value)
                                        <div class="mb-3">
                                            <small class="text-success fw-semibold">
                                                <i class="bi bi-currency-exchange"></i>
                                                PHP {{ number_format($lead->expected_value, 0) }}
                                            </small>
                                        </div>
                                    @endif

                                    @if ($lead->assignedUser)
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="avatar-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                                 style="width: 26px; height: 26px; font-size: 0.75rem; background: #6366f1;">
                                                {{ strtoupper(substr($lead->assignedUser->name, 0, 1)) }}
                                            </div>
                                            <small class="text-muted">{{ $lead->assignedUser->name }}</small>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="d-flex gap-1 pt-2 border-top">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary flex-fill" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-secondary flex-fill" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger flex-fill delete-lead"
                                                data-lead-id="{{ $lead->id }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                    <small class="text-muted">
                                        {{ $lead->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @endforeach

                        {{-- Dynamic Empty State --}}
                        <div class="empty-state text-center py-5 text-muted {{ count($leadsByStatus[$status]) > 0 ? 'd-none' : '' }}">
                            <i class="bi bi-inbox display-6 opacity-25"></i>
                            <p class="mt-3 small">No leads in this stage</p>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Loading Spinner --}}
    <div id="loadingSpinner" class="spinner-border text-primary position-fixed top-50 start-50 translate-middle d-none" role="status" style="width: 2.5rem; height: 2.5rem;">
        <span class="visually-hidden">Loading...</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        const leadsBaseUrl = "{{ url('/leads') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const loadingSpinner = document.getElementById('loadingSpinner');
        const statuses = @json($statuses);

        // ── Sortable (drag & drop) ────────────────────────────────────────────
        document.querySelectorAll('.droppable-zone').forEach(zone => {
            Sortable.create(zone, {
                group: 'leads',
                animation: 150,
                ghostClass: 'card-ghost',
                dragClass: 'card-dragging',
                delay: 100,
                delayOnTouchOnly: true,

                onEnd(evt) {
                    const leadId = evt.item.dataset.leadId;
                    const newStatus = evt.to.dataset.status;
                    const oldStatus = evt.from.dataset.status;

                    if (oldStatus === newStatus) return;

                    // Lost: revert card and navigate to the lost form — no AJAX needed
                    if (newStatus === 'lost') {
                        evt.from.appendChild(evt.item);
                        updateStatusCounts();
                        window.location.href = `${leadsBaseUrl}/${leadId}/lost-form`;
                        return;
                    }

                    updateLeadStatus(leadId, newStatus, evt.from, evt.item);
                },
            });
        });

        // ── AJAX status update ────────────────────────────────────────────────
        function updateLeadStatus(leadId, newStatus, originalZone, cardElement) {
            loadingSpinner.classList.remove('d-none');

            fetch(`${leadsBaseUrl}/kanban/${leadId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(res => res.json())
            .then(data => {
                loadingSpinner.classList.add('d-none');

                if (data.success) {
                    updateStatusCounts();
                    showNotification(data.message ?? 'Lead status updated!', 'success');

                    // Won: prompt to convert
                    if (data.can_convert) {
                        showNotification(
                            `Lead marked as won! <a href="${leadsBaseUrl}/${leadId}" class="alert-link">View lead</a> to convert.`,
                            'success'
                        );
                    }
                } else {
                    // Revert card on failure
                    originalZone.appendChild(cardElement);
                    updateStatusCounts();
                    showNotification(data.message ?? 'Error updating lead status.', 'danger');
                }
            })
            .catch(() => {
                loadingSpinner.classList.add('d-none');
                originalZone.appendChild(cardElement);
                updateStatusCounts();
                showNotification('Network error. Please try again.', 'danger');
            });
        }

        // ── Status count badges + Empty State Handler ────────────────────────
        function updateStatusCounts() {
            statuses.forEach(status => {
                const zone = document.querySelector(`.droppable-zone[data-status="${status}"]`);
                const badge = document.querySelector(`.status-count[data-status="${status}"]`);
                if (zone && badge) {
                    const visibleCards = Array.from(zone.querySelectorAll('.kanban-card'))
                        .filter(card => card.style.display !== 'none').length;
                    badge.textContent = visibleCards;
                }
            });
            updateEmptyStates();
        }

        function updateEmptyStates() {
            document.querySelectorAll('.droppable-zone').forEach(zone => {
                const visibleCards = Array.from(zone.querySelectorAll('.kanban-card'))
                    .filter(card => card.style.display !== 'none').length;

                const emptyEl = zone.querySelector('.empty-state');
                if (emptyEl) {
                    emptyEl.classList.toggle('d-none', visibleCards > 0);
                }
            });
        }

        // ── Toast notification ────────────────────────────────────────────────
        function showNotification(message, type = 'info') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible fade show position-fixed shadow`;
            el.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;';
            el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        // ── Delete lead ───────────────────────────────────────────────────────
        document.querySelectorAll('.delete-lead').forEach(btn => {
            btn.addEventListener('click', function() {
                const leadId = this.dataset.leadId;
                if (!confirm('Are you sure you want to delete this lead?')) return;

                fetch(`${leadsBaseUrl}/${leadId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`.kanban-card[data-lead-id="${leadId}"]`)?.remove();
                        updateStatusCounts();
                        showNotification('Lead deleted successfully!', 'success');
                    } else {
                        showNotification(data.message ?? 'Error deleting lead.', 'danger');
                    }
                })
                .catch(() => showNotification('Network error. Please try again.', 'danger'));
            });
        });

        // ── Client-side filter ────────────────────────────────────────────────
        const searchInput = document.getElementById('search');
        const statusSelect = document.getElementById('status');
        const prioritySelect = document.getElementById('priority');
        const assignedSelect = document.getElementById('assigned_user');

        if (searchInput) searchInput.addEventListener('keyup', filterLeads);
        if (statusSelect) statusSelect.addEventListener('change', filterLeads);
        if (prioritySelect) prioritySelect.addEventListener('change', filterLeads);
        if (assignedSelect) assignedSelect.addEventListener('change', filterLeads);

        function filterLeads() {
            const searchTerm = (searchInput?.value || '').toLowerCase();
            const statusFilter = statusSelect?.value || '';
            const priorityFilter = prioritySelect?.value || '';
            const assignedFilter = assignedSelect?.value || '';

            document.querySelectorAll('.kanban-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                const cardStatus = card.dataset.status ?? '';
                const cardPriority = card.dataset.priority ?? '';
                const cardAssigned = card.dataset.assigned ?? '';

                const matchSearch = !searchTerm || text.includes(searchTerm);
                const matchStatus = !statusFilter || cardStatus === statusFilter;
                const matchPriority = !priorityFilter || cardPriority === priorityFilter;
                const matchAssigned = !assignedFilter || cardAssigned === assignedFilter;

                card.style.display = (matchSearch && matchStatus && matchPriority && matchAssigned) ? '' : 'none';
            });

            updateStatusCounts();
        }

        // Initial load
        window.addEventListener('load', () => {
            updateEmptyStates();
        });
    </script>

    <style>
        .kanban-wrapper {
            display: flex;
            gap: 24px;
            overflow-x: auto;
            padding: 8px 4px 30px;
            background: #f8f9fa;
            border-radius: 16px;
            min-height: 680px;
        }

        .kanban-column {
            min-width: 340px;
            width: 340px;
            flex-shrink: 0;
        }

        .droppable-zone {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px;
            min-height: 620px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f3f5;
        }

        .kanban-card {
            border-radius: 12px;
            cursor: grab;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .kanban-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .card-dragging {
            opacity: 0.6;
            transform: scale(0.98);
        }

        .card-ghost {
            background: #e9ecef;
            border: 2px dashed #adb5bd;
        }

        .avatar-circle {
            border-radius: 50%;
        }

        .hover-underline:hover {
            text-decoration: underline !important;
        }

        .empty-state {
            opacity: 0.6;
        }

        /* Scrollbar */
        .kanban-wrapper::-webkit-scrollbar {
            height: 9px;
        }
        .kanban-wrapper::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }
        .kanban-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .kanban-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media (max-width: 992px) {
            .kanban-column {
                min-width: 310px;
                width: 310px;
            }
        }
    </style>
@endsection