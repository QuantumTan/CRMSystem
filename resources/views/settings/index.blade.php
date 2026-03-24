@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Settings</h1>
            <p class="text-muted mb-0">Manage your account and CRM preferences.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Account Preferences</h2>
                    <p class="text-muted mb-0">Profile update options can be configured from the Profile page.</p>
                    <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-primary mt-3">
                        <i class="bi bi-person me-1"></i> Go to Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">System Preferences</h2>
                    <p class="text-muted mb-0">More settings modules can be added here as your CRM grows.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
