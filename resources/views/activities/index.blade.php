{{-- resources/views/activities/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- Header --}}
        <div class="crm-page-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Team Timeline</div>
                <div class="d-flex align-items-center gap-2">
                    <div>
                        <h4 class="mb-0 fw-semibold">Activity Log</h4>
                        <p class="text-muted mb-0 small">{{ $activities->total() }} total activities recorded</p>
                    </div>
                </div>
            </div>
            @can('create', \App\Models\Activity::class)
                <a href="{{ route('activities.create') }}" class="btn btn-primary crm-module-add-btn">
                    <i class="bi bi-plus-lg me-1"></i> Log Activity
                </a>
            @endcan
        </div>

        {{-- Filter Card --}}
        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-header bg-white border-bottom">
                <div class="card-body p-1">
                    <form action="{{ route('activities.index') }}" method="GET"
                        class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                        <div class="position-relative" style="max-width: 320px; flex: 1;">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" class="form-control form-control-sm ps-5"
                                value="{{ request('search') }}" placeholder="Search activity description...">
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <select name="type" class="form-select form-select-sm w-auto" style="min-width: 140px;">
                                <option value="">All Types</option>
                                <option value="call" @selected(request('type') == 'call')>Call</option>
                                <option value="email" @selected(request('type') == 'email')>Email</option>
                                <option value="meeting" @selected(request('type') == 'meeting')>Meeting</option>
                                <option value="note" @selected(request('type') == 'note')>Note</option>
                            </select>

                            @if (auth()->user()->role !== 'sales')
                                <select name="user_id" class="form-select form-select-sm w-auto" style="min-width: 170px;">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                            <a href="{{ route('activities.index') }}" class="btn btn-outline-primary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Timeline Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if ($activities->isEmpty())
                    <div class="text-center py-5 text-muted crm-empty-state">
                        <i class="bi bi-inbox display-6 opacity-25"></i>
                        <p class="mt-3 mb-0">No activities found.</p>
                    </div>
                @else
                    <div class="activity-timeline">
                        @foreach ($activities as $activity)
                            <article class="timeline-item d-flex gap-3 pb-4">
                                {{-- Icon --}}
                                <div class="timeline-icon timeline-icon-{{ strtolower((string) $activity->activity_type) }} flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bi {{ $activity->type_icon }} fs-6"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                        <div>
                                            @php
                                                $activityTypeClass = match (strtolower((string) $activity->activity_type)) {
                                                    'call' => 'crm-table-status crm-table-status-info',
                                                    'email' => 'crm-table-status crm-table-status-primary',
                                                    'meeting' => 'crm-table-status crm-table-status-warning',
                                                    'note' => 'crm-table-status crm-table-status-muted',
                                                    default => 'crm-table-status crm-table-status-muted',
                                                };
                                            @endphp
                                            <span class="{{ $activityTypeClass }} px-2 py-1">
                                                {{ $activity->type_label }}
                                            </span>
                                            <span class="small text-muted ms-2">
                                                <i class="bi bi-person-circle me-1"></i>
                                                {{ $activity->user->name ?? 'Unknown' }}
                                            </span>

                                            {{-- Linked to Lead or Customer --}}
                                            @if ($activity->lead)
                                                <span class="small text-muted ms-2">
                                                    · Lead:
                                                    <a href="{{ route('leads.show', $activity->lead) }}"
                                                        class="text-muted text-decoration-none fw-medium">
                                                        {{ $activity->lead->name }}
                                                    </a>
                                                </span>
                                            @elseif ($activity->customer)
                                                <span class="small text-muted ms-2">
                                                    · Customer:
                                                    <a href="{{ route('customers.show', $activity->customer) }}"
                                                        class="text-muted text-decoration-none fw-medium">
                                                        {{ $activity->customer->first_name }}
                                                        {{ $activity->customer->last_name }}
                                                    </a>
                                                </span>
                                            @endif
                                        </div>

                                        <small class="text-muted" data-bs-toggle="tooltip"
                                            title="{{ $activity->activity_date->format('M d, Y h:i A') }}">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $activity->activity_date->diffForHumans() }}
                                        </small>
                                    </div>

                                    <p class="mb-0 text-dark small lh-sm" style="white-space: pre-line;">
                                        {{ $activity->description }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($activities->hasPages())
                        <div
                            class="border-top pt-3 mt-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                            <small class="text-muted">
                                Showing {{ $activities->firstItem() }}–{{ $activities->lastItem() }} of
                                {{ $activities->total() }}
                            </small>
                            <div class="d-flex justify-content-center">
                                {{ $activities->withQueryString()->links() }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline Styles (only the vertical line) --}}
    <style>
        .activity-timeline {
            position: relative;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, transparent 0%, var(--color-border) 5%, var(--color-border) 95%, transparent 100%);
            border-radius: var(--radius);
        }

        .timeline-item {
            position: relative;
            transition: all 0.2s ease-in-out;
            border-radius: var(--radius);
            padding: 0.5rem 0.5rem 0.5rem 0;
        }

        .timeline-item:hover {
            background-color: color-mix(in srgb, var(--color-text-heading-light) 2%, transparent);
            transform: scale(1.01);
        }

        .timeline-item:last-child {
            padding-bottom: 0 !important;
        }

        .timeline-item:hover .timeline-icon {
            transform: scale(1.02);
        }

        @media (max-width: 576px) {
            .timeline-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .timeline-icon {
                margin-left: 0.25rem;
            }

            .activity-timeline::before {
                left: 21px;
            }
        }
    </style>

    {{-- Bootstrap Tooltip Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
