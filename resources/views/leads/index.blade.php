@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4" data-lead-index-page
        data-leads-base-url="{{ url('/leads') }}">
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-0 fw-semibold">Leads</h4>
                <p class="text-muted mb-0 small">{{ $leads->total() }} total leads in pipeline</p>
            </div>

            @php
                $currentUser = auth()->user();
                $currencyCode = $systemConfiguration?->currency_code ?? 'PHP';
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
                    <a href="{{ route('leads.create') }}"
                        class="btn btn-primary crm-module-add-btn d-flex align-items-center gap-2">
                        <i class="bi bi-plus"></i>
                        Add Lead
                    </a>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-body p-3">
                <form action="{{ route('leads.index') }}" method="GET"
                    class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                    <div class="position-relative" style="max-width: 300px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted small"></i>
                        <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}" placeholder="Search name, email, phone..."
                            data-lead-filter="search">
                    </div>

                    <select id="status" name="status" class="form-select form-select-sm w-auto"
                        style="min-width: 140px;" data-lead-filter="status">
                        <option value="">All Statuses</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') == $statusOption)>
                                {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                            </option>
                        @endforeach
                    </select>

                    <select id="priority" name="priority" class="form-select form-select-sm w-auto"
                        style="min-width: 130px;" data-lead-filter="priority">
                        <option value="">All Priorities</option>
                        <option value="low" @selected(request('priority') == 'low')>Low</option>
                        <option value="medium" @selected(request('priority') == 'medium')>Medium</option>
                        <option value="high" @selected(request('priority') == 'high')>High</option>
                    </select>

                    <select id="assigned_user" name="assigned_user" class="form-select form-select-sm w-auto"
                        style="min-width: 140px;" data-lead-filter="assigned-user">
                        <option value="">All Users</option>
                        @foreach ($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}" @selected(request('assigned_user') == $assignee->id)>
                                {{ $assignee->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table crm-table table-hover align-middle mb-0 crm-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-muted py-3">Lead ID</th>
                            <th class="small text-muted py-3">Name</th>
                            <th class="small text-muted py-3">Email</th>
                            <th class="small text-muted py-3">Phone Number</th>
                            <th class="small text-muted py-3">Status</th>
                            <th class="small text-muted py-3">Priority</th>
                            <th class="small text-muted py-3">Value</th>
                            <th class="small text-muted py-3">Assigned User</th>
                            <th class="small text-muted py-3">Source</th>
                            <th class="small text-muted py-3">Created At</th>
                            <th class="small text-muted py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr data-lead-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-priority="{{ strtolower($lead->priority) }}"
                                data-assigned="{{ $lead->assigned_user_id ?? '' }}">
                                <td class="small text-muted py-3">{{ $lead->lead_id ?: 'N/A' }}</td>
                                <td class="small text-muted py-3">{{ $lead->name }}</td>
                                <td class="small text-muted py-3">{{ $lead->email ?: 'N/A' }}</td>
                                <td class="small text-muted py-3">{{ $lead->phone ?: 'N/A' }}</td>
                                <td class="py-3">
                                    @php
                                        $leadStatus = strtolower((string) $lead->status);
                                        $leadStatusTarget = route('leads.kanban').'#lead-kanban-card-'.$lead->id;
                                        $leadStatusClass = match ($leadStatus) {
                                            'new' => 'crm-table-status crm-table-status-primary',
                                            'contacted' => 'crm-table-status crm-table-status-info',
                                            'qualified', 'won' => 'crm-table-status crm-table-status-success',
                                            'proposal sent', 'proposal_sent', 'negotiation' => 'crm-table-status crm-table-status-warning',
                                            'lost' => 'crm-table-status crm-table-status-danger',
                                            default => 'crm-table-status crm-table-status-primary',
                                        };
                                    @endphp
                                    <a href="{{ $leadStatusTarget }}" class="text-decoration-none"
                                        title="Open this lead in the Kanban board">
                                        <span class="{{ $leadStatusClass }}">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                                    </a>
                                </td>
                                <td class="py-3">
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
                                <td class="small text-muted py-3">
                                    {{ $lead->expected_value ? $currencyCode . ' ' . number_format($lead->expected_value, 2) : 'N/A' }}
                                </td>
                                <td class="small text-muted py-3">{{ $lead->assignedUser?->name ?: 'Unassigned' }}</td>
                                <td class="small text-muted py-3">{{ $lead->source ?: 'N/A' }}</td>
                                <td class="small text-muted py-3">{{ $lead->created_at->format('M d, Y') }}</td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="btn btn-sm btn-light border text-primary">
                                            View
                                        </a>
                                        @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                                            <a href="{{ route('leads.edit', $lead) }}"
                                                class="btn btn-sm btn-light border text-dark">
                                                Edit
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-light border text-danger delete-lead"
                                                data-delete-lead="{{ $lead->id }}">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">No leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leads->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center px-4 py-3">
                    <small class="text-muted">
                        Showing {{ $leads->firstItem() }}-{{ $leads->lastItem() }} of {{ $leads->total() }} leads
                    </small>
                    {{ $leads->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
