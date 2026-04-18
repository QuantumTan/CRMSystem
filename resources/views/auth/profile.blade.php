@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="crm-page-header mb-4">
        <div class="crm-eyebrow mb-2">Account</div>
        <h2 class="h4 mb-1">My Profile</h2>
        <p class="text-muted mb-0">Your account details and session controls in one place.</p>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="crm-profile-hero-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="h5 mb-1">{{ auth()->user()->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="crm-info-card">
                                <div class="crm-info-label">Full Name</div>
                                <div class="crm-info-value">{{ auth()->user()->name }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="crm-info-card">
                                <div class="crm-info-label">Email Address</div>
                                <div class="crm-info-value">{{ auth()->user()->email }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="crm-info-card">
                                <div class="crm-info-label">Role</div>
                                <div class="crm-info-value text-capitalize">{{ auth()->user()->role }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="crm-info-card">
                                <div class="crm-info-label">Workspace</div>
                                <div class="crm-info-value">{{ config('app.name', 'NexLink CRM') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 mb-2">Session</h2>
                    <p class="text-muted small mb-4">Sign out securely when you are done using the CRM on this device.</p>

                    <form action="{{ route('logout') }}" method="POST" class="d-grid">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
