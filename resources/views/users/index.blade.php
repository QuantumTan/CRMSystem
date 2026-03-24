@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Users</h1>
            <p class="text-muted mb-0">Manage system users.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i> Add User
        </a>
    </div>


            <p class="text-muted">User list will be displayed here.</p>

@endsection
