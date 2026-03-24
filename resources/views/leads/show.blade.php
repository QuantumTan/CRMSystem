@extends('layouts.app')

@section('title', 'Lead Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Lead Details</h1>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted">Lead details will be displayed here.</p>
        </div>
    </div>
@endsection
