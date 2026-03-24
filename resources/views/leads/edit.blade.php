@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Lead</h1>
        </div>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>


            <p class="text-muted">Lead edit form will be displayed here.</p>

@endsection
