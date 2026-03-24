@extends('layouts.app')

@section('title', 'Follow-ups')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Follow-ups</h1>
            <p class="text-muted mb-0">Manage upcoming follow-up tasks.</p>
        </div>
        <a href="{{ route('follow-ups.create') }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i> Add Follow-up
        </a>
    </div>


            <p class="text-muted">Follow-up list will be displayed here.</p>

@endsection
