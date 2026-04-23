@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Administration</div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h2 class="mb-0 fs-3 fw-semibold">Users</h2>
                    <span class="crm-section-tag">
                        Admin
                    </span>
                </div>
                <p class="text-muted mb-0 small">Manage system users and access roles</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary crm-module-add-btn">
                <i class="bi bi-plus-lg"></i> Add User
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-header bg-white border-bottom">
                <div class="card-body p-1">
                    <form action="{{ route('users.index') }}" method="GET"
                        class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                        <div class="position-relative" style="max-width: 320px; flex: 1;">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" class="form-control form-control-sm ps-5"
                                placeholder="Search name or email..." value="{{ request('search') }}">
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <select name="role" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                                <option value="">All Roles</option>
                                @foreach ($roleOptions as $roleOption)
                                    <option value="{{ $roleOption }}" @selected(request('role') === $roleOption)>
                                        {{ ucfirst($roleOption) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table crm-table table-hover align-middle mb-0 crm-data-table crm-table-fixed crm-users-table">
                    <colgroup>
                        <col class="crm-col-user-name">
                        <col class="crm-col-user-email">
                        <col class="crm-col-user-role">
                        <col class="crm-col-user-date">
                        <col class="crm-col-user-actions">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th class="small text-muted py-3">Name</th>
                            <th class="small text-muted py-3">Email</th>
                            <th class="small text-muted py-3">Role</th>
                            <th class="small text-muted py-3">Created</th>
                            <th class="small text-muted py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $userCreatedAt = $user->created_at?->format('M d, Y') ?: 'N/A';
                            @endphp
                            <tr>
                                <td class="py-3 crm-table-cell-truncate" title="{{ $user->name }}{{ $user->id === auth()->id() ? ' (You)' : '' }}">
                                    <div class="fw-semibold text-dark crm-table-cell-truncate">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-muted fw-normal">(You)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $user->email }}">{{ $user->email }}</td>
                                <td class="py-3">
                                    @php
                                        $userRole = strtolower((string) $user->role);
                                        $userRoleClass = match ($userRole) {
                                            'admin' => 'crm-table-status crm-table-status-primary',
                                            'manager' => 'crm-table-status crm-table-status-info',
                                            'sales' => 'crm-table-status crm-table-status-success',
                                            default => 'crm-table-status crm-table-status-muted',
                                        };
                                    @endphp
                                    <span class="{{ $userRoleClass }}">{{ ucfirst($userRole) }}</span>
                                </td>
                                <td class="small text-muted py-3 crm-table-cell-truncate" title="{{ $userCreatedAt }}">{{ $userCreatedAt }}</td>
                                <td class="py-3 crm-table-actions-cell">
                                    <div class="crm-table-actions">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light border text-dark">
                                            Edit
                                        </a>

                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger">Delete</button>
                                            </form>
                                        @else
                                            <span class="btn btn-sm btn-light border text-muted disabled">Current User</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-3">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection
