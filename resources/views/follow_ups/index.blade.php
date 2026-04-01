@extends('layouts.app')

@section('title', 'Follow-ups')

@php
    $isAdmin = auth()->user()?->role === 'admin';
    $isManager = auth()->user()?->role === 'manager';
    $isSales = auth()->user()?->role === 'sales';

    $roleLabel = match (true) {
        $isAdmin => 'Admin',
        $isManager => 'Manager',
        $isSales => 'Sales Staff',
        default => 'Staff',
    };
@endphp

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-semibold">Follow-ups</h4>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                        {{ $roleLabel }}
                    </span>
                </div>
                <p class="text-muted mb-0 small">Manage upcoming follow-up tasks</p>
            </div>
            @can('create', \App\Models\FollowUp::class)
                <a href="{{ route('follow-ups.create') }}" class="btn btn-primary crm-module-add-btn">
                    <i class="bi bi-plus-lg"></i> Add Follow-up
                </a>
            @endcan
        </div>

        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-header bg-white border-bottom p-3">
                <form action="{{ route('follow-ups.index') }}" method="GET"
                    class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                    <div class="position-relative" style="max-width: 320px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search" name="search" class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}" placeholder="Search follow-up title...">
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <select id="status" name="status" class="form-select form-select-sm w-auto" style="min-width: 150px;">
                            <option value="">All Statuses</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>

                    <div class="ms-lg-auto d-flex gap-2">
                        <button type="submit" name="export" value="csv" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download"></i> CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 crm-data-table">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-muted py-3">Title</th>
                            <th class="small text-muted py-3">Due Date</th>
                            <th class="small text-muted py-3">Status</th>
                            <th class="small text-muted py-3">Customer</th>
                            <th class="small text-muted py-3">Lead</th>
                            <th class="small text-muted py-3">Assigned To</th>
                            <th class="small text-muted py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($followUps as $followUp)
                            <tr>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $followUp->title }}</div>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($followUp->description, 70) }}</small>
                                </td>
                                <td class="small text-muted py-3">{{ $followUp->due_date?->format('M d, Y') }}</td>
                                <td class="py-3">
                                    @php
                                        $followUpStatus = strtolower((string) $followUp->status);
                                        $followUpStatusClass = $followUpStatus === 'completed'
                                            ? 'crm-table-status crm-table-status-success'
                                            : 'crm-table-status crm-table-status-warning';
                                    @endphp
                                    <span class="{{ $followUpStatusClass }}">{{ ucfirst($followUpStatus) }}</span>
                                </td>
                                <td class="small text-muted py-3">
                                    @if ($followUp->customer)
                                        {{ $followUp->customer->first_name }} {{ $followUp->customer->last_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="small text-muted py-3">{{ $followUp->lead?->name ?? 'N/A' }}</td>
                                <td class="small text-muted py-3">{{ $followUp->user?->name ?? 'N/A' }}</td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        @can('update', $followUp)
                                            @if ($followUp->status !== 'completed')
                                                <a href="{{ route('follow-ups.edit', $followUp) }}" class="btn btn-sm btn-light border text-dark">Edit</a>
                                                <form action="{{ route('follow-ups.complete', $followUp) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-light border text-success">Complete</button>
                                                </form>
                                            @else
                                                @if ($isAdmin)
                                                    <form action="{{ route('follow-ups.reopen', $followUp) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-light border text-warning">Reopen</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">Locked</span>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-muted small">View only</span>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No follow-ups found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-3">
                {{ $followUps->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection
