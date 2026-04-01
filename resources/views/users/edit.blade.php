@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-semibold">Edit User #{{ $user->id }}</h4>
                <p class="text-muted mb-0 small">Update profile, role, or password.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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
                <form method="POST" action="{{ route('users.update', $user) }}" class="row g-3" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}"
                            autocomplete="name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}"
                            autocomplete="username" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                            <option value="manager" @selected(old('role', $user->role) === 'manager')>Manager</option>
                            <option value="sales" @selected(old('role', $user->role) === 'sales')>Sales Staff</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">New Password <span class="text-muted">(optional)</span></label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password"
                            minlength="8">
                        <small class="text-muted">Leave blank to keep current password. Minimum 8 characters if set.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                            autocomplete="new-password">
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
