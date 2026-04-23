@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isManager = auth()->user()?->role === 'manager';
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Customer View</div>
                <h1 class="h3 mb-1 fw-semibold">Customer #{{ $customer->id }}</h1>
                <p class="text-muted mb-0 small">Review profile, ownership, assignment workflow, and recent activity in one place.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="row g-4 crm-record-layout">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-hero-card mb-4">
                    <div class="card-body p-4 p-lg-5 crm-hero-body">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="crm-hero-avatar">
                                    {{ strtoupper(substr($customer->first_name, 0, 1).substr($customer->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="h4 mb-1">{{ $customer->first_name }} {{ $customer->last_name }}</h2>
                                    <div class="text-muted small mb-2">{{ $customer->email }}</div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <x-status-badge :status="$customer->status" />
                                        <span class="crm-section-tag">{{ ucfirst($customer->assignment_status ?? 'pending') }} Assignment</span>
                                    </div>
                                </div>
                            </div>

                            <div class="crm-action-cluster">
                                @if ($isAdmin || $isSales)
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">Edit Customer</a>
                                @endif
                                @include('customers.partials._delete-form', [
                                    'customer' => $customer,
                                    'buttonClass' => 'btn btn-outline-danger d-inline-flex align-items-center gap-2',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm crm-detail-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Customer Information</h2>
                            <p class="crm-form-section-copy">Core identity and contact information for the account.</p>
                        </div>

                        <div class="crm-detail-grid crm-detail-grid-3">
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Customer ID</span>
                                <div class="crm-detail-value">#{{ $customer->id }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Phone Number</span>
                                <div class="crm-detail-value">{{ $customer->phone ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Company</span>
                                <div class="crm-detail-value">{{ $customer->company ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Address</span>
                                <div class="crm-detail-value">{{ $customer->address ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Created</span>
                                <div class="crm-detail-value">{{ $customer->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Updated</span>
                                <div class="crm-detail-value">{{ $customer->updated_at->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @can('create', \App\Models\Activity::class)
                    @include('activities._form', ['activity' => null, 'lead' => null, 'customer' => $customer])
                @endcan

                @include('activities._timeline', ['activities' => $activities])
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-detail-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Ownership</h2>
                            <p class="crm-form-section-copy">Track who owns this record and its approval state.</p>
                        </div>

                        <div class="crm-detail-grid">
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Assigned User</span>
                                <div class="crm-detail-value">{{ $customer->assignedUser?->name ?: 'Unassigned' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Assignment Status</span>
                                <div class="crm-detail-value">{{ ucfirst($customer->assignment_status ?? 'pending') }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Reviewed By</span>
                                <div class="crm-detail-value">{{ $customer->assignmentReviewer?->name ?: 'Not reviewed yet' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (($isAdmin || $isManager) && $customer->assigned_user_id)
                    <div class="card border-0 shadow-sm crm-form-card mb-4">
                        <div class="card-body">
                            <div class="crm-form-section-head">
                                <h2 class="crm-form-section-title">Assignment Actions</h2>
                                <p class="crm-form-section-copy">Reassign the record or approve and reject the current assignment.</p>
                            </div>

                            <form method="POST" action="{{ route('customers.reassign', $customer) }}" class="mb-3">
                                @csrf
                                @method('PATCH')
                                <label class="form-label fw-medium">Reassign User</label>
                                <select name="assigned_user_id" class="form-select mb-3" required>
                                    @foreach ($assignableUsers as $assignableUser)
                                        <option value="{{ $assignableUser->id }}" @selected((int) $customer->assigned_user_id === (int) $assignableUser->id)>
                                            {{ $assignableUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary w-100">Reassign</button>
                            </form>

                            <div class="crm-form-actions">
                                <form method="POST" action="{{ route('customers.assignment.approve', $customer) }}" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success w-100">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('customers.assignment.reject', $customer) }}" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning w-100">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('customers.partials._modal-delete')
@endsection
