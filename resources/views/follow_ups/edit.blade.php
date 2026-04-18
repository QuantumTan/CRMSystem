@extends('layouts.app')

@section('title', 'Edit Follow-up')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Lead Follow-up</div>
                <h1 class="h4 fw-semibold mb-1">Edit Follow-up</h1>
                <p class="text-muted small mb-0">Update schedule, assignment, and status.</p>
            </div>
            <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        @include('follow_ups._form', ['followUp' => $followUp])
    </div>
@endsection
