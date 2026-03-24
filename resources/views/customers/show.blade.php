@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isManager = auth()->user()?->role === 'manager';
        $status = $customer->assignment_status ?? 'pending';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customer Details</h1>
        </div>
        <div class="d-flex gap-2">
            @if (!$isManager)
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">Edit</a>
            @endif
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Name</div>
                    <div class="fw-semibold">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Email</div>
                    <div>{{ $customer->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Phone</div>
                    <div>{{ $customer->phone }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Company</div>
                    <div>{{ $customer->company ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Assigned Staff</div>
                    <div>{{ $customer->assignedUser?->name ?: 'Unassigned' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Assignment Status</div>
                    <div>
                        <span
                            class="badge {{ $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Reviewed By</div>
                    <div>{{ $customer->assignmentReviewer?->name ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Reviewed At</div>
                    <div>{{ $customer->assignment_reviewed_at?->format('M d, Y h:i A') ?: '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Address</div>
                    <div>{{ $customer->address ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (($isManager || $isAdmin) && $customer->assigned_user_id)
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap align-items-center gap-2">
                <strong class="me-2">Assignment Review:</strong>
                <form method="POST" action="{{ route('customers.assignment.approve', $customer) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-success" type="submit">Approve Assignment</button>
                </form>
                <form method="POST" action="{{ route('customers.assignment.reject', $customer) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-danger" type="submit">Reject Assignment</button>
                </form>
            </div>
        </div>
    @endif

@endsection
