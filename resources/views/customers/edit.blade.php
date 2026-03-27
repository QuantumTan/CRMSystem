@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
    @php
        $isSales = auth()->user()?->role === 'sales';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Edit Customer #{{ $customer->id }}</h4>
                <p class="text-muted mb-0 small">Update customer profile and assignment.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update', $customer) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control"
                            value="{{ old('first_name', $customer->first_name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control"
                            value="{{ old('last_name', $customer->last_name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $customer->email) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $customer->phone) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company" class="form-control"
                            value="{{ old('company', $customer->company) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status', $customer->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    @if ($isSales)
                        <div class="col-md-6">
                            <label class="form-label">Assigned User <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ auth()->user()?->name }} (Sales Staff)"
                                disabled>
                            <input type="hidden" name="assigned_user_id" value="{{ auth()->id() }}">
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="form-label">Assigned User <span class="text-danger">*</span></label>
                            <select name="assigned_user_id" class="form-select" required>
                                <option value="">Select Sales Staff</option>
                                @foreach ($assignableUsers as $assignableUser)
                                    <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_user_id', $customer->assigned_user_id) === (string) $assignableUser->id)>
                                        {{ $assignableUser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
