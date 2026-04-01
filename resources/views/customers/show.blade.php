@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isManager = auth()->user()?->role === 'manager';
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Customer #{{ $customer->id }}</h4>
                <p class="text-muted mb-0 small">View full customer profile and assignment details.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Customer ID</div>
                        <div class="fw-semibold">#{{ $customer->id }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">First Name</div>
                        <div>{{ $customer->first_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Last Name</div>
                        <div>{{ $customer->last_name }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Email</div>
                        <div>{{ $customer->email }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Phone Number</div>
                        <div>{{ $customer->phone }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Company Name</div>
                        <div>{{ $customer->company ?: 'N/A' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Address</div>
                        <div>{{ $customer->address ?: 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Status</div>
                        <div><x-status-badge :status="$customer->status" /></div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Assigned User</div>
                        <div>{{ $customer->assignedUser?->name ?: 'Unassigned' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Assignment Status</div>
                        <div>{{ ucfirst($customer->assignment_status ?? 'pending') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Assignment Reviewed By</div>
                        <div>{{ $customer->assignmentReviewer?->name ?: 'Not reviewed yet' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Created At</div>
                        <div>{{ $customer->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Updated At</div>
                        <div>{{ $customer->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- activity --}}
        <div class="mt-4">
            @can('create', \App\Models\Activity::class)
                @include('activities._form', ['lead' => null, 'customer' => $customer])
            @endcan
            @include('activities._timeline', ['activities' => $activities])
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if ($isAdmin || $isSales)
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">Edit Customer</a>
            @endif

            @if ($isAdmin)
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete Customer</button>
                </form>
            @endif

            @if (($isAdmin || $isManager) && $customer->assigned_user_id)
                <form method="POST" action="{{ route('customers.reassign', $customer) }}" class="d-flex gap-2 align-items-center">
                    @csrf
                    @method('PATCH')
                    <select name="assigned_user_id" class="form-select" style="min-width: 220px;" required>
                        @foreach ($assignableUsers as $assignableUser)
                            <option value="{{ $assignableUser->id }}" @selected((int) $customer->assigned_user_id === (int) $assignableUser->id)>
                                {{ $assignableUser->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Reassign</button>
                </form>

                <form method="POST" action="{{ route('customers.assignment.approve', $customer) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Approve Assignment</button>
                </form>

                <form method="POST" action="{{ route('customers.assignment.reject', $customer) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-warning">Reject Assignment</button>
                </form>
            @endif
        </div>
    </div>
@endsection
