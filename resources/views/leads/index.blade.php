@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- HEADER  --}}
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-0 fw-semibold">Leads</h4>
                <p class="text-muted mb-0 small">{{ $leads->total() }} total leads in pipeline</p>
            </div>

            @php
                $currentUser = auth()->user();
            @endphp

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leads.kanban') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-kanban"></i>
                    Kanban View
                </a>
                <a href="{{ route('leads.index') }}" class="btn btn-dark active d-flex align-items-center gap-2">
                    <i class="bi bi-table"></i>
                    Table View
                </a>

                @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                    <a href="{{ route('leads.create') }}" class="btn btn-primary crm-module-add-btn d-flex align-items-center gap-2">
                        <i class="bi bi-plus"></i>
                        Add Lead
                    </a>
                @endif
            </div>
        </div>

        {{-- FILTER TOOLKIT  --}}
        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-body p-3">
                <form action="{{ route('leads.index') }}" method="GET"
                    class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                    {{-- Search --}}
                    <div class="position-relative" style="max-width: 300px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted small"></i>
                        <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}" placeholder="Search name, email, phone...">
                    </div>

                    {{-- All Statuses --}}
                    <select id="status" name="status" class="form-select form-select-sm w-auto"
                        style="min-width: 140px;">
                        <option value="">All Statuses</option>
                        @foreach ($statusOptions as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>

                    {{-- All Priorities --}}
                    <select id="priority" name="priority" class="form-select form-select-sm w-auto"
                        style="min-width: 130px;">
                        <option value="">All Priorities</option>
                        <option value="low" @selected(request('priority') == 'low')>Low</option>
                        <option value="medium" @selected(request('priority') == 'medium')>Medium</option>
                        <option value="high" @selected(request('priority') == 'high')>High</option>
                    </select>

                    {{-- All Users --}}
                    <select id="assigned_user" name="assigned_user" class="form-select form-select-sm w-auto"
                        style="min-width: 140px;">
                        <option value="">All Users</option>
                        @foreach ($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}" @selected(request('assigned_user') == $assignee->id)>
                                {{ $assignee->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>

                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 crm-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 fw-semibold small text-uppercase text-muted">Lead</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Contact</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Status</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Priority</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Value</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Assigned To</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Source</th>
                            <th class="py-3 fw-semibold small text-uppercase text-muted">Created</th>
                            <th class="pe-4 py-3 fw-semibold small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr data-lead-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-priority="{{ strtolower($lead->priority) }}"
                                data-assigned="{{ $lead->assigned_user_id ?? '' }}">

                                {{-- Lead Name --}}
                                <td class="ps-4">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="fw-semibold text-decoration-none text-dark">
                                        {{ $lead->name }}
                                    </a>
                                </td>

                                {{-- Contact --}}
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if ($lead->email)
                                            <a href="mailto:{{ $lead->email }}"
                                                class="text-muted text-decoration-none small">
                                                <i class="bi bi-envelope me-1"></i>{{ Str::limit($lead->email, 22) }}
                                            </a>
                                        @endif
                                        @if ($lead->phone)
                                            <a href="tel:{{ $lead->phone }}"
                                                class="text-muted text-decoration-none small">
                                                <i class="bi bi-telephone me-1"></i>{{ $lead->phone }}
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $leadStatus = strtolower((string) $lead->status);
                                        $leadStatusClass = match ($leadStatus) {
                                            'new' => 'crm-table-status crm-table-status-primary',
                                            'contacted' => 'crm-table-status crm-table-status-info',
                                            'qualified', 'won' => 'crm-table-status crm-table-status-success',
                                            'proposal sent', 'proposal_sent', 'negotiation' => 'crm-table-status crm-table-status-warning',
                                            'lost' => 'crm-table-status crm-table-status-danger',
                                            default => 'crm-table-status crm-table-status-primary',
                                        };
                                    @endphp
                                    <span class="{{ $leadStatusClass }}">{{ ucfirst($lead->status) }}</span>
                                </td>

                                {{-- Priority --}}
                                <td>
                                    @php
                                        $leadPriority = strtolower((string) $lead->priority);
                                        $leadPriorityClass = match ($leadPriority) {
                                            'high' => 'crm-table-status crm-table-status-danger',
                                            'medium' => 'crm-table-status crm-table-status-warning',
                                            'low' => 'crm-table-status crm-table-status-success',
                                            default => 'crm-table-status crm-table-status-primary',
                                        };
                                    @endphp
                                    <span class="{{ $leadPriorityClass }}">{{ ucfirst($lead->priority) }}</span>
                                </td>

                                {{-- Expected Value --}}
                                <td>
                                    @if ($lead->expected_value)
                                        <span class="text-success fw-semibold small">
                                            PHP {{ number_format($lead->expected_value, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Assigned To --}}
                                <td>
                                    @if ($lead->assignedUser)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($lead->assignedUser->name, 0, 1)) }}
                                            </div>
                                            <span class="small text-muted">{{ $lead->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Source --}}
                                <td>
                                    @if ($lead->source)
                                        <span class="crm-table-meta">{{ $lead->source }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Created --}}
                                <td>
                                    <span class="text-muted small" title="{{ $lead->created_at->format('M d, Y h:i A') }}">
                                        {{ $lead->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="btn btn-sm btn-outline-primary crm-action-btn" title="View">
                                            View
                                        </a>
                                        @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                                            <a href="{{ route('leads.edit', $lead) }}"
                                                class="btn btn-sm btn-outline-warning crm-action-btn" title="Edit">
                                                Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-lead crm-action-btn"
                                                data-lead-id="{{ $lead->id }}" title="Delete">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">No leads found</p>
                                    @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                                        <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="bi bi-plus-lg"></i> Add your first lead
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($leads->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center px-4 py-3">
                    <small class="text-muted">
                        Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }} leads
                    </small>
                    {{ $leads->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- client-side live filtering + enhanced delete --}}
    <script>
        const leadsBaseUrl = "{{ url('/leads') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        //  Delete lead
        document.querySelectorAll('.delete-lead').forEach(btn => {
            btn.addEventListener('click', function() {
                const leadId = this.dataset.leadId;
                if (!confirm('Are you sure you want to delete this lead? This cannot be undone.')) return;

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
                            const row = document.querySelector(`tr[data-lead-id="${leadId}"]`);
                            if (row) row.remove();
                            showNotification('Lead deleted successfully!', 'success');
                        } else {
                            showNotification(data.message ?? 'Error deleting lead.', 'danger');
                        }
                    })
                    .catch(() => showNotification('Network error. Please try again.', 'danger'));
            });
        });

        //  Client-side live filter 
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

            document.querySelectorAll('tbody tr[data-lead-id]').forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.dataset.status ?? '';
                const rowPriority = row.dataset.priority ?? '';
                const rowAssigned = row.dataset.assigned ?? '';

                const matchSearch = !searchTerm || text.includes(searchTerm);
                const matchStatus = !statusFilter || rowStatus === statusFilter;
                const matchPriority = !priorityFilter || rowPriority === priorityFilter;
                const matchAssigned = !assignedFilter || rowAssigned === assignedFilter;

                row.style.display = (matchSearch && matchStatus && matchPriority && matchAssigned) ? '' : 'none';
            });
        }

        //  Toast notification  
        function showNotification(message, type = 'info') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible fade show position-fixed shadow`;
            el.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;';
            el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        // Initial load - ensure any pre-filled filters are applied instantly (optional but nice)
        window.addEventListener('load', () => {
            if (searchInput?.value || statusSelect?.value || prioritySelect?.value || assignedSelect?.value) {
                filterLeads();
            }
        });
    </script>

    <style>
        .avatar-circle {
            width: 26px;
            height: 26px;
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
    </style>
@endsection
