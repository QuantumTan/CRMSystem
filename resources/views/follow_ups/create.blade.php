@extends('layouts.app')

@section('title', 'Add Follow-up')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-semibold mb-1">Add Follow-up</h1>
                <p class="text-muted small mb-0">Create a reminder for a customer or lead.</p>
            </div>
            <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        @include('follow_ups._form')
    </div>
@endsection
