@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Leads</h1>
            <p class="text-muted mb-0">Manage your sales pipeline.</p>
        </div>
        <a href="{{ route('leads.create') }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i> Add Lead
        </a>
    </div>


            <p class="text-muted">Lead list will be displayed here.</p>

@endsection
