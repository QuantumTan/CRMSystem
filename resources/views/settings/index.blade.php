@extends('layouts.app')

@section('title', 'System Configuration')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">System Configuration</h1>
                <p class="text-muted mb-0">Admin-only controls for CRM identity, lead defaults, and account recovery behavior.</p>
            </div>
            <div class="text-muted small">
                Password reset links currently expire after
                <strong>{{ $systemConfiguration->password_reset_expire_minutes ?? 60 }} minutes</strong>.
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h2 class="h5 mb-1">Business Identity</h2>
                            <p class="text-muted mb-0 small">These values define the name and contact details used by your CRM team.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="app_name" class="form-label fw-medium">Application Name</label>
                                <input
                                    id="app_name"
                                    type="text"
                                    name="app_name"
                                    class="form-control @error('app_name') is-invalid @enderror"
                                    value="{{ old('app_name', $systemConfiguration->app_name ?? config('app.name', 'NexLink CRM')) }}"
                                    required
                                >
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="currency_code" class="form-label fw-medium">Currency Code</label>
                                <input
                                    id="currency_code"
                                    type="text"
                                    name="currency_code"
                                    class="form-control @error('currency_code') is-invalid @enderror"
                                    value="{{ old('currency_code', $systemConfiguration->currency_code ?? 'PHP') }}"
                                    maxlength="10"
                                    required
                                >
                                @error('currency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="company_email" class="form-label fw-medium">Company Email</label>
                                <input
                                    id="company_email"
                                    type="email"
                                    name="company_email"
                                    class="form-control @error('company_email') is-invalid @enderror"
                                    value="{{ old('company_email', $systemConfiguration->company_email) }}"
                                    placeholder="support@company.com"
                                >
                                @error('company_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="company_phone" class="form-label fw-medium">Company Phone</label>
                                <input
                                    id="company_phone"
                                    type="text"
                                    name="company_phone"
                                    class="form-control @error('company_phone') is-invalid @enderror"
                                    value="{{ old('company_phone', $systemConfiguration->company_phone) }}"
                                    placeholder="+63 912 345 6789"
                                >
                                @error('company_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="company_address" class="form-label fw-medium">Company Address</label>
                                <textarea
                                    id="company_address"
                                    name="company_address"
                                    rows="3"
                                    class="form-control @error('company_address') is-invalid @enderror"
                                    placeholder="Office address or headquarters"
                                >{{ old('company_address', $systemConfiguration->company_address) }}</textarea>
                                @error('company_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h2 class="h5 mb-1">Pipeline Defaults</h2>
                            <p class="text-muted mb-0 small">These defaults are preselected when admins and sales staff create new leads.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="default_lead_status" class="form-label fw-medium">Default Lead Status</label>
                                <select
                                    id="default_lead_status"
                                    name="default_lead_status"
                                    class="form-select @error('default_lead_status') is-invalid @enderror"
                                    required
                                >
                                    @foreach ($statusOptions as $statusOption)
                                        <option
                                            value="{{ $statusOption }}"
                                            @selected(old('default_lead_status', $systemConfiguration->default_lead_status ?? 'new') === $statusOption)
                                        >
                                            {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_lead_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="default_lead_priority" class="form-label fw-medium">Default Lead Priority</label>
                                <select
                                    id="default_lead_priority"
                                    name="default_lead_priority"
                                    class="form-select @error('default_lead_priority') is-invalid @enderror"
                                    required
                                >
                                    @foreach ($priorityOptions as $priorityOption)
                                        <option
                                            value="{{ $priorityOption }}"
                                            @selected(old('default_lead_priority', $systemConfiguration->default_lead_priority ?? 'medium') === $priorityOption)
                                        >
                                            {{ ucfirst($priorityOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_lead_priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h2 class="h5 mb-1">Security</h2>
                            <p class="text-muted mb-0 small">Control how long password reset emails remain valid after they are sent.</p>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="password_reset_expire_minutes" class="form-label fw-medium">Reset Link Expiration (Minutes)</label>
                                <input
                                    id="password_reset_expire_minutes"
                                    type="number"
                                    name="password_reset_expire_minutes"
                                    min="15"
                                    max="1440"
                                    class="form-control @error('password_reset_expire_minutes') is-invalid @enderror"
                                    value="{{ old('password_reset_expire_minutes', $systemConfiguration->password_reset_expire_minutes ?? 60) }}"
                                    required
                                >
                                @error('password_reset_expire_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="rounded-3 border bg-light p-3 small text-muted">
                                    Reset links on the forgot-password page automatically use this value on the next request.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Profile Shortcut</h2>
                        <p class="text-muted small mb-3">Personal account updates still live on the profile page.</p>
                        <a href="{{ route('profile') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person me-1"></i> Go to Profile
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Current Snapshot</h2>
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted">App Name</dt>
                            <dd class="col-7">{{ $systemConfiguration->app_name ?? config('app.name', 'NexLink CRM') }}</dd>

                            <dt class="col-5 text-muted">Currency</dt>
                            <dd class="col-7">{{ $systemConfiguration->currency_code ?? 'PHP' }}</dd>

                            <dt class="col-5 text-muted">Lead Status</dt>
                            <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $systemConfiguration->default_lead_status ?? 'new')) }}</dd>

                            <dt class="col-5 text-muted">Priority</dt>
                            <dd class="col-7">{{ ucfirst($systemConfiguration->default_lead_priority ?? 'medium') }}</dd>

                            <dt class="col-5 text-muted">Reset Expiry</dt>
                            <dd class="col-7">{{ $systemConfiguration->password_reset_expire_minutes ?? 60 }} mins</dd>
                        </dl>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-1"></i> Save Configuration
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
