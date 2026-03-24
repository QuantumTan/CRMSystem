@extends('layouts.app')

@section('title', 'Edit Follow-up')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Follow-up</h1>
        </div>
        <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>


            <p class="text-muted">Follow-up edit form will be displayed here.</p>

@endsection
