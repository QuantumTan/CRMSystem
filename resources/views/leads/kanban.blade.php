@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    @php
        $currentUser = auth()->user();
        $canDragLeads = $currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales'));
        $focusedStatus = request('status');
    @endphp

    @include('leads.partials._modal-delete')

    <div class="container-fluid px-3 px-md-4 py-4" data-lead-kanban-page
        data-leads-base-url="{{ url('/leads') }}" data-can-drag-leads="{{ $canDragLeads ? 'true' : 'false' }}">

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
                <a href="{{ route('leads.kanban') }}" class="btn btn-dark active d-flex align-items-center gap-2">
                    <i class="bi bi-kanban-fill"></i>
                    Kanban View
                </a>
                <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-table"></i>
                    Table View
                </a>

                @if ($canDragLeads)
                    <a href="{{ route('leads.create') }}"
                        class="btn btn-primary crm-module-add-btn d-flex align-items-center gap-2">
                        <i class="bi bi-plus"></i>
                        Add Lead
                    </a>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-header bg-white border-bottom">
                <div class="card-body p-1">
                    <form action="{{ route('leads.kanban') }}" method="GET"
                        class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                        <div class="position-relative" style="max-width: 320px; flex: 1;">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                                value="{{ request('search') }}" placeholder="Search name, email, phone...">
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <select id="status" name="status" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(request('status') == $statusOption)>
                                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                    </option>
                                @endforeach
                            </select>

                            <select id="priority" name="priority" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                                <option value="">All Priorities</option>
                                <option value="low" @selected(request('priority') == 'low')>Low</option>
                                <option value="medium" @selected(request('priority') == 'medium')>Medium</option>
                                <option value="high" @selected(request('priority') == 'high')>High</option>
                            </select>

                            <select id="assigned_user" name="assigned_user" class="form-select form-select-sm w-auto"
                                style="min-width: 140px;">
                                <option value="">All Users</option>
                                @foreach ($users as $assignee)
                                    <option value="{{ $assignee->id }}" @selected(request('assigned_user') == $assignee->id)>
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                            <a href="{{ route('leads.kanban') }}" class="btn btn-outline-primary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="kanban-wrapper">
            @foreach ($statuses as $status)
                @php
                    $statusAnchor = 'status-'.\Illuminate\Support\Str::slug($status, '-');
                    $isFocusedStatus = $focusedStatus === $status;
                    $statusBadgeClass = match (strtolower((string) $status)) {
                        'new' => 'crm-table-status crm-table-status-primary',
                        'contacted' => 'crm-table-status crm-table-status-info',
                        'qualified', 'won' => 'crm-table-status crm-table-status-success',
                        'proposal sent', 'proposal_sent', 'negotiation' => 'crm-table-status crm-table-status-warning',
                        'lost' => 'crm-table-status crm-table-status-danger',
                        default => 'crm-table-status crm-table-status-muted',
                    };
                @endphp
                <div id="{{ $statusAnchor }}" class="kanban-column {{ $isFocusedStatus ? 'kanban-column-focused' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="{{ $statusBadgeClass }} px-3 py-2"
                                style="font-size: 0.9rem;">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary fw-normal px-3 py-1">
                            {{ count($leadsByStatus[$status]) }}
                        </span>
                    </div>

                    <div class="kanban-cards droppable-zone" data-status="{{ $status }}">
                        @foreach ($leadsByStatus[$status] as $lead)
                            <div id="lead-kanban-card-{{ $lead->id }}" class="kanban-card card border-0 shadow-sm draggable-card"
                                draggable="true" data-lead-id="{{ $lead->id }}"
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
                                        @php
                                            $priorityBadgeClass = match (strtolower((string) $lead->priority)) {
                                                'high', 'critical' => 'crm-table-status crm-table-status-danger',
                                                'medium' => 'crm-table-status crm-table-status-warning',
                                                'low' => 'crm-table-status crm-table-status-success',
                                                default => 'crm-table-status crm-table-status-muted',
                                            };
                                        @endphp
                                        <span class="{{ $priorityBadgeClass }} px-2 py-1"
                                            style="font-size: 0.8rem;">
                                            {{ ucfirst($lead->priority) }}
                                        </span>

                                        @if ($lead->source)
                                            <span class="badge bg-info-subtle text-info px-2 py-1">
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
                                                style="width: 26px; height: 26px; font-size: 0.75rem; background: var(--color-accent);">
                                                {{ strtoupper(substr($lead->assignedUser->name, 0, 1)) }}
                                            </div>
                                            <small class="text-muted">{{ $lead->assignedUser->name }}</small>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-1 pt-2 border-top">
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="btn btn-sm btn-outline-primary crm-action-btn" title="View">
                                            View
                                        </a>
                                        @if ($canDragLeads)
                                            <a href="{{ route('leads.edit', $lead) }}"
                                                class="btn btn-sm btn-outline-warning crm-action-btn" title="Edit">
                                                Edit
                                            </a>
                                        @endif
                                        @include('leads.partials._delete-trigger', [
                                            'lead' => $lead,
                                            'buttonClass' => 'btn btn-sm btn-outline-danger crm-action-btn d-inline-flex align-items-center gap-1',
                                            'buttonLabel' => 'Delete',
                                        ])
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                    <small class="text-muted">
                                        {{ $lead->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @endforeach

                        @if (count($leadsByStatus[$status]) === 0)
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

    <style>
        .kanban-wrapper {
            display: flex;
            gap: 24px;
            overflow-x: auto;
            padding: 8px 4px 30px;
            background: var(--color-surface-page);
            border-radius: var(--radius-lg);
            min-height: 680px;
        }

        .kanban-column {
            min-width: 340px;
            width: 340px;
            flex-shrink: 0;
            scroll-margin-left: 20px;
        }

        .kanban-column-focused .droppable-zone {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent) 12%, transparent);
        }

        .droppable-zone {
            background: var(--color-surface-card);
            border-radius: var(--radius-lg);
            padding: 16px;
            min-height: 620px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: 0 1px 3px color-mix(in srgb, var(--color-text-heading-light) 5%, transparent);
            border: 1px solid var(--color-border);
        }

        .kanban-card {
            border-radius: var(--radius-lg);
            cursor: grab;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px color-mix(in srgb, var(--color-text-heading-light) 6%, transparent);
            scroll-margin: 2rem 7rem;
        }

        .kanban-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px color-mix(in srgb, var(--color-text-heading-light) 12%, transparent);
        }

        .kanban-card:target,
        .kanban-card-targeted {
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-accent) 35%, transparent), 0 12px 24px color-mix(in srgb, var(--color-accent) 12%, transparent);
            z-index: 2;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .card-dragging {
            opacity: 0.6;
            transform: scale(0.98);
        }

        .card-ghost {
            background: var(--color-border);
            border: 2px dashed var(--color-border);
        }

        .avatar-circle {
            border-radius: var(--radius-pill);
        }

        .hover-underline:hover {
            text-decoration: underline !important;
        }

        .empty-state {
            opacity: 0.6;
        }

        .kanban-wrapper::-webkit-scrollbar {
            height: 9px;
        }

        .kanban-wrapper::-webkit-scrollbar-track {
            background: var(--color-border);
            border-radius: var(--radius-pill);
        }

        .kanban-wrapper::-webkit-scrollbar-thumb {
            background: var(--color-border);
            border-radius: var(--radius-pill);
        }

        .kanban-wrapper::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-muted);
        }

        @media (max-width: 992px) {
            .kanban-column {
                min-width: 310px;
                width: 310px;
            }
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush
