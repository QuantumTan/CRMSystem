@extends('layouts.app')

@section('title', 'Edit Activity')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Activity Tracking</div>
                <h1 class="h3 mb-1 fw-semibold">Edit Activity</h1>
                <p class="text-muted mb-0 small">Update the recorded interaction while keeping the format consistent with new activity entries.</p>
            </div>
            <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        @include('activities._form', [
            'activity' => $activity,
            'lead' => $activity->lead,
            'customer' => $activity->customer,
            'leads' => collect(),
            'customers' => collect(),
        ])
    </div>
@endsection
