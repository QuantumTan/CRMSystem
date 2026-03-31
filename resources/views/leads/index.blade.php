@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    {{-- <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Leads</h4>
                <p class="text-muted mb-0 small">Track prospects, ownership, and conversion to customers</p>
            </div>
            <a href="{{ route('leads.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Lead
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <form action="{{ route('leads.index') }}" method="GET" class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                    <div class="position-relative" style="max-width: 320px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}"
                            placeholder="Search name, email, phone, source..."
                        >
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <select id="status" name="status" class="form-select form-select-sm w-auto" style="min-width: 150px;">
                            <option value="">All Statuses</option>
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                    {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                </option>
                            @endforeach
                        </select>

                        <select id="priority" name="priority" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                            <option value="">All Priorities</option>
                            @foreach ($priorityOptions as $priorityOption)
                                <option value="{{ $priorityOption }}" @selected(request('priority') === $priorityOption)>
                                    {{ ucfirst($priorityOption) }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-muted py-3">Lead ID</th>
                            <th class="small text-muted py-3">Prospect Name</th>
                            <th class="small text-muted py-3">Contact Information</th>
                            <th class="small text-muted py-3">Source</th>
                            <th class="small text-muted py-3">Expected Value</th>
                            <th class="small text-muted py-3">Status</th>
                            <th class="small text-muted py-3">Priority</th>
                            <th class="small text-muted py-3">Assigned User</th>
                            <th class="small text-muted py-3">Customer</th>
                            <th class="small text-muted py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="small text-muted py-3">#{{ $lead->id }}</td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $lead->name }}</div>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($lead->notes, 40) }}</small>
                                </td>
                                <td class="small text-muted py-3">
                                    <div>{{ $lead->email ?: 'N/A' }}</div>
                                    <div>{{ $lead->phone ?: 'N/A' }}</div>
                                </td>
                                <td class="small text-muted py-3">{{ $lead->source ?: 'N/A' }}</td>
                                <td class="small text-muted py-3">{{ $lead->expected_value ? 'PHP '.number_format((float) $lead->expected_value, 2) : 'N/A' }}</td>
                                <td class="py-3">
                                    <form action="{{ route('leads.update-status', $lead) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach ($statusOptions as $statusOption)
                                                <option value="{{ $statusOption }}" @selected($lead->status === $statusOption)>
                                                    {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                                    </form>
                                </td>
                                <td class="py-3">
                                    <form action="{{ route('leads.set-priority', $lead) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="priority" class="form-select form-select-sm">
                                            @foreach ($priorityOptions as $priorityOption)
                                                <option value="{{ $priorityOption }}" @selected($lead->priority === $priorityOption)>
                                                    {{ ucfirst($priorityOption) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                                    </form>
                                </td>
                                <td class="py-3">
                                    <form action="{{ route('leads.assign', $lead) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="assigned_user_id" class="form-select form-select-sm">
                                            <option value="">Unassigned</option>
                                            @foreach ($assignableUsers as $assignableUser)
                                                <option value="{{ $assignableUser->id }}" @selected((int) $lead->assigned_user_id === (int) $assignableUser->id)>
                                                    {{ $assignableUser->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                                    </form>
                                </td>
                                <td class="small text-muted py-3">
                                    {{ $lead->customer ? $lead->customer->first_name.' '.$lead->customer->last_name : 'Not Converted' }}
                                </td>
                                <td class="py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-light border">Details</a>
                                        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-light border">Edit</a>
                                        @if ($lead->customer_id === null)
                                            <form action="{{ route('leads.convert', $lead) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Convert</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Delete this lead?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">No leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top py-3 mt-3">
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
    </div> --}}

    {{-- TODO: implement a kanban --}}

<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-semibold">Leads</h4>
            <p class="text-muted mb-0 small">{{ $leads->total() }} total leads in pipeline</p>
        </div>
        @php $user = auth()->user(); @endphp
        <div class="d-flex gap-2">
            <a href="{{ route('leads.kanban') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-kanban"></i> Kanban View
            </a>
            @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Lead
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('leads.index') }}" method="GET"
                class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                <div class="position-relative" style="max-width: 300px; flex: 1;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted small"></i>
                    <input type="text" name="search" class="form-control form-control-sm ps-5"
                        value="{{ request('search') }}" placeholder="Search name, email, phone...">
                </div>

                <select name="status" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                    <option value="">All Statuses</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
                    @endforeach
                </select>

                <select name="priority" class="form-select form-select-sm w-auto" style="min-width: 130px;">
                    <option value="">All Priorities</option>
                    <option value="High"   @selected(request('priority') == 'High')>High</option>
                    <option value="Medium" @selected(request('priority') == 'Medium')>Medium</option>
                    <option value="Low"    @selected(request('priority') == 'Low')>Low</option>
                </select>

                <select name="assigned_user" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('assigned_user') == $user->id)>
                            {{ $user->name }}
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

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                        <tr>
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
                                <span class="badge rounded-pill"
                                    style="background-color: {{ getStatusColor($lead->status) }}; font-size: 0.75rem;">
                                    {{ $lead->status }}
                                </span>
                            </td>

                            {{-- Priority --}}
                            <td>
                                <span class="badge rounded-pill"
                                    style="background-color: {{ getPriorityColor($lead->priority) }}; font-size: 0.75rem; color: white;">
                                    {{ $lead->priority }}
                                </span>
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
                                    <span class="badge bg-light text-dark border small">{{ $lead->source }}</span>
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
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                                        <a href="{{ route('leads.edit', $lead) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-lead"
                                            data-lead-id="{{ $lead->id }}" title="Delete">
                                            <i class="bi bi-trash"></i>
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
                                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="bi bi-plus-lg"></i> Add your first lead
                                </a>
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

{{-- Delete confirmation --}}
<script>
    document.querySelectorAll('.delete-lead').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this lead? This cannot be undone.')) return;
            const leadId = this.dataset.leadId;
            fetch(`/leads/${leadId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
            });
        });
    });
</script>

<style>
    .avatar-circle {
        width: 26px; height: 26px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: bold; flex-shrink: 0;
    }
    .table > tbody > tr:last-child > td { border-bottom: none; }
</style>



@endsection
