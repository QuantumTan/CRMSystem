@extends('layouts.app')

@section('title', 'Activities')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Activities</h1>
            <p class="text-muted mb-0">Track all customer interactions.</p>
        </div>
        <a href="{{ route('activities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus me-1"></i> Log Activity
        </a>
    </div>


            <p class="text-muted">Activity list will be displayed here.</p>
   
@endsection
