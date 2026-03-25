@extends('layouts.app')

@section('title', 'Lead Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Lead #{{ $lead->id }}</h1>
            <p class="text-muted mb-0">Prospect details and conversion context.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Customer or Prospect Name</div>
                    <div class="fw-semibold">{{ $lead->name }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Contact Information</div>
                    <div>{{ $lead->email ?: 'N/A' }}</div>
                    <div>{{ $lead->phone ?: 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Source</div>
                    <div>{{ $lead->source ?: 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Status</div>
                    <div>{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Priority</div>
                    <div>{{ ucfirst($lead->priority) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Expected Value</div>
                    <div>{{ $lead->expected_value ? 'PHP '.number_format((float) $lead->expected_value, 2) : 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Assigned User</div>
                    <div>{{ $lead->assignedUser?->name ?: 'Unassigned' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Customer Link</div>
                    <div>
                        @if ($lead->customer)
                            {{ $lead->customer->first_name }} {{ $lead->customer->last_name }}
                        @else
                            Not converted
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Notes</div>
                    <div>{{ $lead->notes ?: 'No notes provided.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-primary">Edit Lead</a>
        @if ($lead->customer_id === null)
            <form action="{{ route('leads.convert', $lead) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">Convert to Customer</button>
            </form>
        @endif
        <form action="{{ route('leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Delete this lead?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete</button>
        </form>
        @if ($lead->customer)
            <a href="{{ route('customers.show', $lead->customer) }}" class="btn btn-outline-secondary">Open Customer</a>
        @endif
    </div>
@endsection
