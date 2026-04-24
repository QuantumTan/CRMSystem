@extends('layouts.app')

@section('title', 'Add Lead')

@section('content')
    @php
        $defaultLeadStatus = old('status', $systemConfiguration?->default_lead_status ?? config('crm.default_lead_status', 'new'));
        $defaultLeadPriority = old('priority', $systemConfiguration?->default_lead_priority ?? config('crm.default_lead_priority', 'medium'));
        $currencyCode = $systemConfiguration?->currency_code ?? config('crm.currency_code', 'PHP');
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Pipeline Management</div>
                <h1 class="h3 mb-1 fw-semibold">Add Lead</h1>
                <p class="text-muted mb-0 small">Capture a new sales opportunity using the same structure as every other record form.</p>
            </div>
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
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

        <form action="{{ route('leads.store') }}" method="POST" class="crm-form-shell row g-4">
            @csrf

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Lead Details</h2>
                            <p class="crm-form-section-copy">Capture the lead identity, contact info, and supporting context for the sales team.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Customer or Prospect Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Source</label>
                                <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" value="{{ old('source') }}" placeholder="Website, Referral, Event...">
                                @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" placeholder="Any additional context about this lead...">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Pipeline Settings</h2>
                            <p class="crm-form-section-copy">Set the stage, priority, value, and owner for this opportunity.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}" @selected($defaultLeadStatus === $statusOption)>
                                            {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    @foreach ($priorityOptions as $priorityOption)
                                        <option value="{{ $priorityOption }}" @selected($defaultLeadPriority === $priorityOption)>
                                            {{ ucfirst($priorityOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Expected Value</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">{{ $currencyCode }}</span>
                                    <input type="number" name="expected_value" class="form-control @error('expected_value') is-invalid @enderror" min="0" step="0.01" value="{{ old('expected_value') }}" placeholder="0.00">
                                    @error('expected_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Assigned User</label>
                                <select name="assigned_user_id" class="form-select @error('assigned_user_id') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach ($assignableUsers as $assignableUser)
                                        <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_user_id') === (string) $assignableUser->id)>
                                            {{ $assignableUser->name }} ({{ ucfirst($assignableUser->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Link Existing Customer (Optional)</label>
                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">None</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-primary flex-grow-1">Save Lead</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
