@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
    @php $user = auth()->user(); @endphp
    @if (!($user && ($user->hasRole('admin') || $user->hasRole('sales'))))
        <div class="alert alert-danger">You do not have permission to edit leads.</div>
        @return
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Lead #{{ $lead->id }}</h1>
            <p class="text-muted mb-0">Update prospect details and pipeline state.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('leads.update', $lead) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Customer or Prospect Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source', $lead->source) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}" @selected(old('status', $lead->status) === $statusOption)>
                                {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select" required>
                        @foreach ($priorityOptions as $priorityOption)
                            <option value="{{ $priorityOption }}" @selected(old('priority', $lead->priority) === $priorityOption)>
                                {{ ucfirst($priorityOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Expected Value</label>
                    <input type="number" name="expected_value" class="form-control" min="0" step="0.01" value="{{ old('expected_value', $lead->expected_value) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Assigned User</label>
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
                    <label class="form-label">Linked Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">None</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $lead->customer_id) === (string) $customer->id)>
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes', $lead->notes) }}</textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Lead</button>
                </div>
            </form>
        </div>
    </div>

@endsection
