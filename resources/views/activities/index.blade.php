{{-- resources/views/activities/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- Header --}}
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h4 class="mb-0 fw-semibold">Activity Log</h4>
                <p class="text-muted mb-0 small">{{ $activities->total() }} total activities recorded</p>
            </div>
            <a href="{{ route('activities.create') }}" class="btn btn-dark btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Log Activity
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('activities.index') }}" method="GET"
                    class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                    {{-- Search --}}
                    <div class="position-relative" style="max-width: 300px; flex: 1;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted small"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-5"
                            value="{{ request('search') }}" placeholder="Search description...">
                    </div>

                    {{-- Type --}}
                    <select name="type" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                        <option value="">All Types</option>
                        <option value="call" @selected(request('type') == 'call')>Call</option>
                        <option value="email" @selected(request('type') == 'email')>Email</option>
                        <option value="meeting" @selected(request('type') == 'meeting')>Meeting</option>
                        <option value="note" @selected(request('type') == 'note')>Note</option>
                    </select>

                    {{-- User --}}
                    <select name="user_id" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                        <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if ($activities->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-6 opacity-25"></i>
                        <p class="mt-3 mb-0">No activities found.</p>
                    </div>
                @else
                    <div class="activity-timeline">
                        @foreach ($activities as $activity)
                            <div class="timeline-item d-flex gap-3 pb-4">

                                <div class="timeline-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle text-white"
                                    style="width: 36px; height: 36px; background-color: {{ $activity->type_color }}; margin-top: 2px;">
                                    <i class="bi {{ $activity->type_icon }}" style="font-size: 0.8rem;"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                                        <div>
                                            <span class="badge rounded-pill fw-medium px-2 py-1"
                                                style="background-color: {{ $activity->type_color }}; color: white; font-size: 0.75rem;">
                                                {{ $activity->type_label }}
                                            </span>
                                            <span class="small text-muted ms-2">
                                                by {{ $activity->user->name ?? 'Unknown' }}
                                            </span>
                                            {{-- Linked to --}}
                                            @if ($activity->lead)
                                                <span class="small text-muted ms-1">
                                                    · Lead:
                                                    <a href="{{ route('leads.show', $activity->lead) }}"
                                                        class="text-muted text-decoration-none fw-medium">
                                                        {{ $activity->lead->name }}
                                                    </a>
                                                </span>
                                            @elseif ($activity->customer)
                                                <span class="small text-muted ms-1">
                                                    · Customer:
                                                    <a href="{{ route('customers.show', $activity->customer) }}"
                                                        class="text-muted text-decoration-none fw-medium">
                                                        {{ $activity->customer->first_name }}
                                                        {{ $activity->customer->last_name }}
                                                    </a>
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted"
                                            title="{{ $activity->activity_date->format('M d, Y h:i A') }}">
                                            {{ $activity->activity_date->diffForHumans() }}
                                        </small>
                                    </div>

                                    <p class="mb-0 small text-dark" style="white-space: pre-line;">
                                        {{ $activity->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($activities->hasPages())
                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Showing {{ $activities->firstItem() }}–{{ $activities->lastItem() }} of
                                {{ $activities->total() }}
                            </small>
                            {{ $activities->withQueryString()->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>

    <style>
        .activity-timeline {
            position: relative;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 17px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #f1f3f5;
        }

        .timeline-item:last-child {
            padding-bottom: 0 !important;
        }
    </style>
@endsection
