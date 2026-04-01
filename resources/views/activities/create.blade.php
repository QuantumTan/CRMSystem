{{-- resources/views/activities/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Log Activity')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-0">Log Activity</h4>
            <p class="text-muted small mb-0">Record a call, email, meeting, or note.</p>
        </div>
        <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div style="max-width: 640px;">
        @include('activities._form', [
            'lead' => null,
            'customer' => null,
            'leads' => $leads,
            'customers' => $customers,
        ])
    </div>

</div>
@endsection