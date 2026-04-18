@extends('layouts.app')

@section('title', 'Log Activity')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Activity Tracking</div>
                <h1 class="h3 mb-1 fw-semibold">Log Activity</h1>
                <p class="text-muted mb-0 small">Record a call, email, meeting, or note using the same workflow everywhere in the CRM.</p>
            </div>
            <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        @include('activities._form', [
            'activity' => null,
            'lead' => null,
            'customer' => null,
            'leads' => $leads,
            'customers' => $customers,
        ])
    </div>
@endsection
