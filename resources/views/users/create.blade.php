@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Administration</div>
                <h1 class="h3 mb-1 fw-semibold">Add User</h1>
                <p class="text-muted mb-0 small">Create a new system user account with the right role and access level.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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

        <form method="POST" action="{{ route('users.store') }}" class="crm-form-shell row g-4" autocomplete="off">
            @csrf

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">User Profile</h2>
                            <p class="crm-form-section-copy">Add the core account details your team member needs to sign in and start working.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="name" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="username" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Access & Role</h2>
                            <p class="crm-form-section-copy">Choose the level of access this user should have across the CRM.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Select role</option>
                                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                    <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                                    <option value="sales" @selected(old('role') === 'sales')>Sales Staff</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-note-box mb-4">
                    Passwords must be at least 8 characters. Access permissions are enforced automatically by the app’s role policies.
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-success flex-grow-1">Create User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
