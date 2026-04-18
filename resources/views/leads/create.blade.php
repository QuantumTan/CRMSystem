@extends('layouts.app')

@section('title', 'Add Lead')

@section('content')
    @php
        $defaultLeadStatus = old('status', $systemConfiguration?->default_lead_status ?? 'new');
        $defaultLeadPriority = old('priority', $systemConfiguration?->default_lead_priority ?? 'medium');
        $currencyCode = $systemConfiguration?->currency_code ?? 'PHP';
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-semibold">Add Lead</h1>
                <p class="text-muted mb-0 small">Capture a new sales opportunity.</p>
            </div>
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- GLOBAL ERRORS (Optional, but good as a fallback) --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- FORM --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('leads.store') }}" method="POST" class="row g-3">
                    @csrf

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

                    <div class="col-md-4">
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

                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Expected Value</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">{{ $currencyCode }}</span>
                            <input type="number" name="expected_value" class="form-control @error('expected_value') is-invalid @enderror" min="0" step="0.01" value="{{ old('expected_value') }}" placeholder="0.00">
                            @error('expected_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
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

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Link Existing Customer (Optional)</label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">None</option>
                            {{-- Check if $customers is set, to prevent errors if not passed from controller --}}
                            @if(isset($customers))
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" placeholder="Any additional context about this lead...">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('leads.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
