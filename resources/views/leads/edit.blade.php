@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-semibold">Edit Lead #{{ $lead->id }}</h1>
                <p class="text-muted mb-0 small">Update prospect details and pipeline state.</p>
            </div>
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        {{-- ERRORS --}}
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
                <form action="{{ route('leads.update', $lead) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Customer or Prospect Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Source</label>
                        <input type="text" name="source" class="form-control"
                            value="{{ old('source', $lead->source) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}" @selected(old('status', $lead->status) === $statusOption)>
                                    {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach ($priorityOptions as $priorityOption)
                                <option value="{{ $priorityOption }}" @selected(old('priority', $lead->priority) === $priorityOption)>
                                    {{ ucfirst($priorityOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Expected Value</label>
                        <div class="input-group">
                            <span class="input-group-text">PHP</span>
                            <input type="number" name="expected_value" class="form-control" min="0" step="0.01"
                                value="{{ old('expected_value', $lead->expected_value) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Assigned User</label>
                        <select name="assigned_user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach ($assignableUsers as $assignableUser)
                                <option value="{{ $assignableUser->id }}" @selected((string) old('assigned_user_id', $lead->assigned_user_id) === (string) $assignableUser->id)>
                                    {{ $assignableUser->name }} ({{ ucfirst($assignableUser->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Linked Customer</label>
                        <select name="converted_to_customer_id" class="form-select">
                            <option value="">None</option>
                            {{-- Check if $customers is passed from the controller to prevent the 500 error --}}
                            @if (isset($customers))
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((string) old('converted_to_customer_id', $lead->converted_to_customer_id) === (string) $customer->id)>
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text">Usually linked automatically via the "Convert to Customer" button.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes', $lead->notes) }}</textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
