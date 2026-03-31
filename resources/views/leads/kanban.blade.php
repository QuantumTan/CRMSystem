@extends('layouts.app')

@section('title', 'Leads Kanban')



@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- Header --}}
        @php $user = auth()->user(); @endphp
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Leads Kanban Board</h4>
                <p class="text-muted mb-0 small">Track prospects across the sales pipeline</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-table"></i> Table View
                </a>
                @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                    <a href="{{ route('leads.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Lead
                    </a>
                @endif
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <form action="{{ route('leads.kanban') }}" method="GET"
                    class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                    <div class="position-relative" style="max-width: 320px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}" placeholder="Search name, email, phone...">
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <select id="assigned_user" name="assigned_user" class="form-select form-select-sm w-auto"
                            style="min-width: 150px;">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('assigned_user') == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('leads.kanban') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kanban Board --}}
        <div class="kanban-wrapper">
            @foreach ($statuses as $status)
                <div class="kanban-column">

                    {{-- Column Header --}}
                    <div class="card border-0 mb-3">
                        <div class="card-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <span class="badge"
                                        style="background-color: {{ getStatusColor($status) }}; font-size: 0.85rem;">
                                        {{ $status }}
                                    </span>
                                </h5>
                                {{--
                                    FIX: Use a unique class + data attribute pair.
                                    Previously the selector `.status-count[data-status]` was
                                    accidentally matching the droppable zone too.
                                --}}
                                <span class="badge bg-secondary column-count" data-column="{{ $status }}">
                                    {{ count($leadsByStatus[$status]) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{--
                        FIX: Removed flex-wrap and flex-basis hacks.
                        A simple flex-direction: column is all that's needed for
                        SortableJS to work correctly as a vertical list.
                    --}}
                    <div class="kanban-cards droppable-zone" data-status="{{ $status }}">

                        @forelse ($leadsByStatus[$status] as $lead)
                            <div class="kanban-card card border-0 shadow-sm draggable-card"
                                draggable="true"
                                data-lead-id="{{ $lead->id }}"
                                data-assigned="{{ $lead->assigned_user_id ?? '' }}"
                                style="border-left: 4px solid {{ getPriorityColor($lead->priority) }};">

                                <div class="card-body p-3">

                                    {{-- Lead Name --}}
                                    <h6 class="card-title mb-2 fw-bold">
                                        <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none text-dark">
                                            {{ $lead->name }}
                                        </a>
                                    </h6>

                                    {{-- Contact Details --}}
                                    <div class="small mb-2">
                                        @if ($lead->email)
                                            <div class="text-muted mb-1">
                                                <i class="bi bi-envelope"></i>
                                                <a href="mailto:{{ $lead->email }}" class="text-muted text-decoration-none">
                                                    {{ Str::limit($lead->email, 25) }}
                                                </a>
                                            </div>
                                        @endif

                                        @if ($lead->phone)
                                            <div class="text-muted mb-1">
                                                <i class="bi bi-telephone"></i>
                                                <a href="tel:{{ $lead->phone }}" class="text-muted text-decoration-none">
                                                    {{ $lead->phone }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Priority Badge --}}
                                    <div class="mb-2">
                                        <span class="badge"
                                            style="background-color: {{ getPriorityColor($lead->priority) }}; color: white;">
                                            {{ $lead->priority }}
                                        </span>
                                    </div>

                                    {{-- Expected Value --}}
                                    @if ($lead->expected_value)
                                        <div class="mb-2">
                                            <small class="text-success fw-bold">
                                                <i class="bi bi-cash-coin"></i>
                                                PHP {{ number_format($lead->expected_value, 2) }}
                                            </small>
                                        </div>
                                    @endif

                                    {{-- Assigned User --}}
                                    @if ($lead->assignedUser)
                                        <div class="mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle">
                                                    {{ strtoupper(substr($lead->assignedUser->name, 0, 1)) }}
                                                </div>
                                                <small class="text-muted">{{ $lead->assignedUser->name }}</small>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Source --}}
                                    @if ($lead->source)
                                        <div class="mb-2">
                                            <small class="badge bg-info text-dark">{{ $lead->source }}</small>
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="mt-3 d-flex gap-2">
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="btn btn-sm btn-outline-primary" title="View details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                                            <a href="{{ route('leads.edit', $lead) }}"
                                                class="btn btn-sm btn-outline-warning" title="Edit lead">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-lead"
                                                data-lead-id="{{ $lead->id }}" title="Delete lead">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-top">
                                    <small class="text-muted">
                                        Created {{ $lead->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>

                        @empty
                            <div class="empty-state text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No leads in this status</p>
                            </div>
                        @endforelse

                    </div>{{-- /.droppable-zone --}}
                </div>{{-- /.kanban-column --}}
            @endforeach
        </div>{{-- /.kanban-wrapper --}}
    </div>

    {{-- Loading Spinner --}}
    <div id="loadingSpinner"
        class="spinner-border position-fixed top-50 start-50 translate-middle d-none"
        role="status" style="z-index: 9999;">
        <span class="visually-hidden">Loading...</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        const loadingSpinner = document.getElementById('loadingSpinner');

        // ─── Drag & Drop ────────────────────────────────────────────────────────────
        // FIX: Removed flex-wrap options. SortableJS needs a clean vertical flex
        // container. The previous flex-wrap + flex-basis:100% setup made cards
        // appear side-by-side and broke the ghost placeholder positioning.
        document.querySelectorAll('.droppable-zone').forEach(zone => {
            Sortable.create(zone, {
                group: 'leads',
                animation: 150,
                ghostClass: 'card-ghost',
                dragClass: 'card-dragging',
                delay: 100,
                delayOnTouchOnly: true,
                onEnd(evt) {
                    const leadId   = evt.item.dataset.leadId;
                    const newStatus = evt.to.dataset.status;
                    const oldStatus = evt.from.dataset.status;
                    if (oldStatus !== newStatus) {
                        updateLeadStatus(leadId, newStatus);
                    }
                    // Always refresh counts after any move (even within same column)
                    updateColumnCounts();
                }
            });
        });

        // ─── AJAX Status Update ──────────────────────────────────────────────────────
        function updateLeadStatus(leadId, newStatus) {
            loadingSpinner.classList.remove('d-none');

            fetch(`/leads/kanban/${leadId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(r => r.json())
            .then(data => {
                loadingSpinner.classList.add('d-none');
                if (data.success) {
                    showNotification('Lead status updated successfully!', 'success');
                } else {
                    showNotification('Error updating lead status', 'danger');
                    location.reload();
                }
            })
            .catch(() => {
                loadingSpinner.classList.add('d-none');
                showNotification('Network error — reloading', 'danger');
                location.reload();
            });
        }

        // ─── Column Count Update ─────────────────────────────────────────────────────
        // FIX: Changed selector from `.status-count[data-status]` (which could
        // accidentally match the droppable zone div) to `.column-count[data-column]`.
        // Also now only counts VISIBLE cards so the count stays accurate during
        // client-side filtering.
        function updateColumnCounts() {
            document.querySelectorAll('.droppable-zone').forEach(zone => {
                const status  = zone.dataset.status;
                const visible = zone.querySelectorAll('.kanban-card:not([style*="display: none"])').length;
                const badge   = document.querySelector(`.column-count[data-column="${status}"]`);
                if (badge) badge.textContent = visible;
            });
        }

        // ─── Notifications ───────────────────────────────────────────────────────────
        function showNotification(message, type = 'info') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            el.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        // ─── Delete ──────────────────────────────────────────────────────────────────
        document.querySelectorAll('.delete-lead').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!confirm('Are you sure you want to delete this lead?')) return;

                const leadId = this.dataset.leadId;
                fetch(`/leads/${leadId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // FIX: Remove the card by lead ID, not by the button's own selector
                        const card = document.querySelector(`.kanban-card[data-lead-id="${leadId}"]`);
                        if (card) card.remove();
                        updateColumnCounts();
                        showNotification('Lead deleted successfully!', 'success');
                    } else {
                        showNotification('Error deleting lead', 'danger');
                    }
                })
                .catch(() => showNotification('Network error while deleting', 'danger'));
            });
        });

        // ─── Client-side Filter ──────────────────────────────────────────────────────
        // FIX: Previously the filter ran on 'keyup' for search and 'change' for
        // assigned_user, but a server-side form submit was also wired to the same
        // inputs. Kept client-side filtering for instant feedback; the form button
        // still does a proper server-side filter.
        // Also fixed: hidden cards are now correctly excluded from the count.
        function filterLeads() {
            const searchTerm     = document.getElementById('search').value.toLowerCase().trim();
            const assignedFilter = document.getElementById('assigned_user').value;

            document.querySelectorAll('.kanban-card').forEach(card => {
                const text     = card.textContent.toLowerCase();
                const assigned = card.dataset.assigned || '';

                const matchSearch   = !searchTerm || text.includes(searchTerm);
                const matchAssigned = !assignedFilter || assigned === assignedFilter;

                card.style.display = (matchSearch && matchAssigned) ? '' : 'none';
            });

            updateColumnCounts();
        }

        document.getElementById('search').addEventListener('input', filterLeads);
        document.getElementById('assigned_user').addEventListener('change', filterLeads);
    </script>

    <style>
        /* ── Wrapper ─────────────────────────────────────────────────── */
        .kanban-wrapper {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 15px;
            padding-bottom: 20px;
            background-color: #e8eaed;
            border-radius: 8px;
        }

        /* ── Column ──────────────────────────────────────────────────── */
        .kanban-column {
            flex-shrink: 0;
            min-width: 320px;
            width: 360px;
            border-radius: 8px;
        }

        /* ── Drop Zone ───────────────────────────────────────────────── */
        /*
         * FIX: was flex-wrap: wrap + flex-basis: 100% which caused cards to
         * flow into multiple columns. Now it's a simple vertical flex list.
         */
        .droppable-zone {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 500px;
            padding: 12px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 8px;
        }

        /* ── Cards ───────────────────────────────────────────────────── */
        .kanban-card {
            border-radius: 8px;
            cursor: grab;
            user-select: none;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
            /* No flex-basis or flex-shrink overrides — let the column handle it */
        }

        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.card-dragging {
            opacity: 0.5;
            background-color: #e9ecef;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .card-ghost {
            opacity: 0;
        }

        /* ── Avatar ──────────────────────────────────────────────────── */
        .avatar-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ── Empty state ─────────────────────────────────────────────── */
        .empty-state {
            flex: 1;
        }

        /* ── Scrollbar ───────────────────────────────────────────────── */
        .kanban-wrapper::-webkit-scrollbar        { height: 8px; }
        .kanban-wrapper::-webkit-scrollbar-track  { background: #f1f1f1; border-radius: 10px; }
        .kanban-wrapper::-webkit-scrollbar-thumb  { background: #888; border-radius: 10px; }
        .kanban-wrapper::-webkit-scrollbar-thumb:hover { background: #555; }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .kanban-column {
                min-width: 280px;
                width: 300px;
            }
        }
    </style>
@endsection