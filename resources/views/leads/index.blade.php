@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    @include('leads.partials._modal-delete')

    <div class="container-fluid px-3 px-md-4 py-4" data-lead-index-page>
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
            <div class="card-header bg-white border-bottom">
                <div class="card-body p-1">
                    <form action="{{ route('leads.index') }}" method="GET"
                        class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                        <div class="position-relative" style="max-width: 320px; flex: 1;">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                                value="{{ request('search') }}" placeholder="Search name, email, phone..."
                                data-lead-filter="search">
                        </div>

                        <div class="d-flex flex-wrap gap-2">
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

                            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                            <a href="{{ route('leads.index') }}" class="btn btn-outline-primary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table crm-table table-hover align-middle mb-0 crm-data-table crm-table-fixed crm-leads-table">
                    <colgroup>
                        <col class="crm-col-lead-id">
                        <col class="crm-col-lead-name">
                        <col class="crm-col-lead-email">
                        <col class="crm-col-lead-phone">
                        <col class="crm-col-lead-status">
                        <col class="crm-col-lead-priority">
                        <col class="crm-col-lead-value">
                        <col class="crm-col-lead-assignee">
                        <col class="crm-col-lead-source">
                        <col class="crm-col-lead-date">
                        <col class="crm-col-lead-actions">
                    </colgroup>
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
                            @php
                                $leadId = $lead->lead_id ?: 'N/A';
                                $leadEmail = $lead->email ?: 'N/A';
                                $leadPhone = $lead->phone ?: 'N/A';
                                $leadValue = $lead->expected_value ? $currencyCode . ' ' . number_format($lead->expected_value, 2) : 'N/A';
                                $leadAssignee = $lead->assignedUser?->name ?: 'Unassigned';
                                $leadSource = $lead->source ?: 'N/A';
                                $leadCreatedAt = $lead->created_at->format('M d, Y');
                            @endphp
                            <tr data-lead-id="{{ $lead->id }}" data-status="{{ $lead->status }}"
                                data-priority="{{ strtolower($lead->priority) }}"
                                data-assigned="{{ $lead->assigned_user_id ?? '' }}">
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadId }}">{{ $leadId }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $lead->name }}">{{ $lead->name }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadEmail }}">{{ $leadEmail }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadPhone }}">{{ $leadPhone }}</td>
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
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadValue }}">{{ $leadValue }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadAssignee }}">{{ $leadAssignee }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadSource }}">{{ $leadSource }}</td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $leadCreatedAt }}">{{ $leadCreatedAt }}</td>
                                <td class="py-3 crm-table-actions-cell">
                                    <div class="crm-table-actions">
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="btn btn-sm btn-light border text-primary">
                                            View
                                        </a>
                                        @if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('sales')))
                                            <a href="{{ route('leads.edit', $lead) }}"
                                                class="btn btn-sm btn-light border text-dark">
                                                Edit
                                            </a>
                                            @include('leads.partials._delete-trigger', [
                                                'lead' => $lead,
                                                'buttonClass' => 'btn btn-sm btn-light border text-danger d-inline-flex align-items-center gap-1',
                                                'buttonLabel' => 'Delete',
                                            ])
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
