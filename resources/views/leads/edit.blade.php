@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
    @php
        $currencyCode = $systemConfiguration?->currency_code ?? config('crm.currency_code', 'PHP');
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Pipeline Management</div>
                <h1 class="h3 mb-1 fw-semibold">Edit Lead #{{ $lead->id }}</h1>
                <p class="text-muted mb-0 small">Update prospect details, ownership, and current pipeline stage.</p>
            </div>
            <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
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

        <form action="{{ route('leads.update', $lead) }}" method="POST" class="crm-form-shell row g-4">
            @csrf
            @method('PUT')

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-form-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Lead Details</h2>
                            <p class="crm-form-section-copy">Keep the lead profile accurate as it moves through your sales workflow.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Customer or Prospect Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $lead->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Source</label>
                                <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" value="{{ old('source', $lead->source) }}">
                                @error('source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $lead->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $lead->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5">{{ old('notes', $lead->notes) }}</textarea>
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
                            <p class="crm-form-section-copy">Update stage, priority, value, and ownership in one place.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}" @selected(old('status', $lead->status) === $statusOption)>
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
                                        <option value="{{ $priorityOption }}" @selected(old('priority', $lead->priority) === $priorityOption)>
                                            {{ ucfirst($priorityOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Expected Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $currencyCode }}</span>
                                    <input type="number" name="expected_value" class="form-control @error('expected_value') is-invalid @enderror" min="0" step="0.01" value="{{ old('expected_value', $lead->expected_value) }}">
                                    @error('expected_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Assigned User</label>
                                <select name="assigned_user_id" class="form-select @error('assigned_user_id') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach ($assignableUsers as $assignableUser)
                                        <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_user_id', $lead->assigned_user_id) === (string) $assignableUser->id)>
                                            {{ $assignableUser->name }} ({{ ucfirst($assignableUser->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">Linked Customer</label>
                                <select name="converted_to_customer_id" class="form-select">
                                    <option value="">None</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected((string) old('converted_to_customer_id', $lead->converted_to_customer_id) === (string) $customer->id)>
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="form-text">Usually linked automatically from the convert-to-customer action.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-success flex-grow-1">Update Lead</button>
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection
