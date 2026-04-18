@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    @php
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Customer Management</div>
                <h1 class="h3 mb-1 fw-semibold">Add Customer</h1>
                <p class="text-muted mb-0 small">Create a new customer record with consistent contact and assignment details.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger crm-alert mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.store') }}" class="crm-form-shell row g-4">
            @csrf

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Profile Details</h2>
                            <p class="crm-form-section-copy">Capture the customer identity, contact details, and company information.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Company Name</label>
                                <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company') }}" placeholder="Optional company or organization">
                                @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="4" placeholder="Office address or full customer address">{{ old('address') }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Assignment</h2>
                            <p class="crm-form-section-copy">Set the active status and assign ownership to the right sales staff member.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if ($isSales)
                                <div class="col-12">
                                    <label class="form-label fw-medium">Assigned User <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ auth()->user()?->name }} (Sales Staff)" disabled>
                                    <input type="hidden" name="assigned_user_id" value="{{ auth()->id() }}">
                                </div>
                            @else
                                <div class="col-12">
                                    <label class="form-label fw-medium">Assigned User <span class="text-danger">*</span></label>
                                    <select name="assigned_user_id" class="form-select @error('assigned_user_id') is-invalid @enderror" required>
                                        <option value="">Select Sales Staff</option>
                                        @foreach ($assignableUsers as $assignableUser)
                                            <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_user_id') === (string) $assignableUser->id)>
                                                {{ $assignableUser->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="crm-note-box mb-4">
                    Customer assignment review is handled automatically for admins, managers, and sales based on your current workflow rules.
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-primary flex-grow-1">Save Customer</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
