@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
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
    </div>
        <div class="card-footer bg-white border-top py-3 mt-3">
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
    </div>
            <p class="text-muted">Lead list will be displayed here.</p>

@endsection
