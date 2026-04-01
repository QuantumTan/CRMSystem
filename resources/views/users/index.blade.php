@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 class="mb-0 fs-3 fw-semibold">Users</h2>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                        Admin
                    </span>
                </div>
                <p class="text-muted mb-0 small">Manage system users and access roles</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add User
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                            <tr>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                    @if ($user->id === auth()->id())
                                        <small class="text-muted">You</small>
                                    @endif
                                </td>
                                <td class="small text-muted py-3">{{ $user->email }}</td>
                                <td class="py-3">
                                    <span class="badge text-bg-secondary">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td class="small text-muted py-3">{{ $user->created_at?->format('M d, Y') }}</td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-end gap-2">
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
        </div>

        <div class="card-footer bg-white border-top py-3 mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>

@endsection
