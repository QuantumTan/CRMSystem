@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    @php
        $currentUser = auth()->user();
    @endphp

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

                @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                    <a href="{{ route('leads.create') }}" 
                       class="btn btn-primary crm-module-add-btn d-flex align-items-center gap-2">
                        <i class="bi bi-plus"></i>
                        Add Lead
                    </a>
                @endif
            </div>
        </div>

        {{-- FILTER TOOLKIT (Now powered entirely by traditional GET request) --}}
        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
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
                        @foreach ($users as $assignee)
                            <option value="{{ $assignee->id }}" @selected(request('assigned_user') == $assignee->id)>
                                {{ $assignee->name }}
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
                        <span class="badge bg-secondary-subtle text-secondary fw-normal px-3 py-1">
                            {{ count($leadsByStatus[$status]) }}
                        </span>
                    </div>

                    {{-- Droppable Zone --}}
                    <div class="kanban-cards droppable-zone" data-status="{{ $status }}">

                        @foreach ($leadsByStatus[$status] as $lead)
                            <div class="kanban-card card border-0 shadow-sm draggable-card" draggable="true"
                                 data-lead-id="{{ $lead->id }}" 
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
                                            <span class="badge px-2 py-1" style="background-color: rgba(37, 99, 235, 0.12); color: #1d4ed8;">
                                                {{ $lead->source }}
                                            </span>
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

                                    {{-- Actions (Now using standard Forms) --}}
                                    <div class="d-flex flex-wrap gap-1 pt-2 border-top">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-primary crm-action-btn" title="View">
                                            View
                                        </a>
                                        @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                                            <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-warning crm-action-btn" title="Edit">
                                                Edit
                                            </a>
                                            <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="d-inline-flex" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger crm-action-btn" title="Delete">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                    <small class="text-muted">
                                        {{ $lead->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @endforeach

                        {{-- Empty State --}}
                        @if(count($leadsByStatus[$status]) === 0)
                            <div class="empty-state text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 opacity-25"></i>
                                <p class="mt-3 small">No leads in this stage</p>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        const canDragLeads = @json($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')));
        const leadsBaseUrl = "{{ url('/leads') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (!canDragLeads) {
            document.querySelectorAll('.draggable-card').forEach((card) => {
                card.setAttribute('draggable', 'false');
                card.style.cursor = 'default';
            });
        }

        // ── Sortable (drag & drop) with Traditional Form Submit ──────────
        document.querySelectorAll('.droppable-zone').forEach(zone => {
            if (!canDragLeads) {
                return;
            }

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

                    // Standard Redirect for lost reason
                    if (newStatus === 'lost') {
                        window.location.href = `${leadsBaseUrl}/${leadId}/lost-form`;
                        return;
                    }

                    // Dynamically create a standard form and submit it to trigger a page reload
                    const form = document.createElement('form');
                    form.method = 'POST';
                    // Example action string: /leads/kanban/5/status
                    form.action = `${leadsBaseUrl}/kanban/${leadId}/status`; 

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PATCH';

                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = newStatus;

                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    form.appendChild(statusInput);
                    document.body.appendChild(form);
                    
                    // Trigger traditional form submit (page reload)
                    form.submit();
                },
            });
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