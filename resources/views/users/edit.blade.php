@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Administration</div>
                <h1 class="h3 mb-1 fw-semibold">Edit User #{{ $user->id }}</h1>
                <p class="text-muted mb-0 small">Update the account profile, assigned role, or password.</p>
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

        <form method="POST" action="{{ route('users.update', $user) }}" class="crm-form-shell row g-4" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">User Profile</h2>
                            <p class="crm-form-section-copy">Keep the user information accurate for authentication and team visibility.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" autocomplete="username" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">New Password <span class="text-muted">(optional)</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
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
                            <p class="crm-form-section-copy">Choose the correct role for this account and review password change guidance.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                                    <option value="manager" @selected(old('role', $user->role) === 'manager')>Manager</option>
                                    <option value="sales" @selected(old('role', $user->role) === 'sales')>Sales Staff</option>
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-note-box mb-4">
                    Leave the password fields empty if you want to keep the current password unchanged.
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-success flex-grow-1">Update User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
