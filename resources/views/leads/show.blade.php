@extends('layouts.app')

@section('title', $lead->name)

@php
    $statuses = ['New', 'Contacted', 'Qualified', 'Proposal Sent', 'Negotiation', 'Won', 'Lost'];
    $currentStatusIndex = array_search($lead->status, $statuses);
    $user = auth()->user();

    // Helper arrays to map your custom badge colors to Bootstrap contextual classes
    $statusColors = [
        'new' => 'primary',
        'contacted' => 'info',
        'qualified' => 'success',
        'proposal sent' => 'warning',
        'negotiation' => 'danger',
        'won' => 'success',
        'lost' => 'danger',
    ];

    $priorityColors = [
        'high' => 'danger',
        'medium' => 'warning',
        'low' => 'secondary',
    ];
@endphp

@push('styles')
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">

    <style>
        /* Keep only the font families, let Bootstrap handle the rest */
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f8f9fa;
        }

        .font-serif {
            font-family: 'DM Serif Display', serif;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 py-lg-5">

        {{-- header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <li class="breadcrumb-item"><a href="{{ route('leads.index') }}"
                                class="text-decoration-none text-muted">Leads</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $lead->name }}</li>
                    </ol>
                </nav>
                <h1 class="font-serif display-6 mb-0 text-dark">{{ $lead->name }}</h1>
            </div>

            @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                <div class="d-flex flex-wrap gap-2">
                    
                    {{-- ── CONVERT TO CUSTOMER BUTTON ───────────────────────── --}}
                    @if(strtolower($lead->status) === 'won' && empty($lead->customer_id))
                        <form action="{{ route('leads.convert', $lead) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 fw-medium" 
                                    style="font-size: 0.875rem;" 
                                    onclick="return confirm('Are you sure you want to convert this lead into a customer?');">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M10.5 4.5l3 3m0 0l-3 3m3-3H2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Convert to Customer
                            </button>
                        </form>
                    @elseif(!empty($lead->customer_id))
                        <a href="{{ route('customers.show', $lead->customer_id) }}" class="btn btn-info text-white d-inline-flex align-items-center gap-2 px-3 fw-medium" style="font-size: 0.875rem;">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                                <circle cx="8" cy="5.5" r="3" />
                                <path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                            </svg>
                            View Customer
                        </a>
                    @endif

                    <a href="{{ route('leads.edit', $lead) }}"
                        class="btn btn-light border shadow-sm d-inline-flex align-items-center gap-2 px-3 fw-medium"
                        style="font-size: 0.875rem;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.6">
                            <path d="M11 2l3 3-9 9H2v-3L11 2z" />
                        </svg>
                        Edit
                    </a>
                    <button type="button"
                        class="btn btn-danger text-danger bg-danger bg-opacity-10 border-danger border-opacity-25 d-inline-flex align-items-center gap-2 px-3 fw-medium"
                        id="deleteLeadBtn" style="font-size: 0.875rem;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.6">
                            <path d="M2 4h12M6 4V2h4v2M5 4v9a1 1 0 001 1h4a1 1 0 001-1V4" />
                        </svg>
                        Delete
                    </button>
                </div>
            @endif
        </div>

        {{-- ── Two-column  --}}
        <div class="row g-4">

            {{-- LEFT: Profile + Notes  --}}
            <div class="col-12 col-lg-4">

                {{-- Profile card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">

                        <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                            <div class="bg-dark text-white rounded-3 d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0"
                                style="width: 48px; height: 48px;">
                                {{ strtoupper(substr($lead->name, 0, 2)) }}
                            </div>
                            <div>
                                <h5 class="font-serif mb-1 text-dark">{{ $lead->name }}</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $statusSlug = strtolower(str_replace(' ', '-', $lead->status));
                                        $prioritySlug = strtolower($lead->priority);

                                        $sColor = $statusColors[strtolower($lead->status)] ?? 'secondary';
                                        $pColor = $priorityColors[$prioritySlug] ?? 'secondary';
                                    @endphp
                                    <span
                                        class="badge rounded-pill bg-{{ $sColor }} bg-opacity-10 text-{{ $sColor }} fw-medium px-2 py-1">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                    <span
                                        class="badge rounded-pill bg-{{ $pColor }} bg-opacity-10 text-{{ $pColor }} fw-medium px-2 py-1">
                                        {{ ucfirst($lead->priority) }} Priority
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            @if ($lead->email)
                                <div class="d-flex align-items-start gap-3">
                                    <div
                                        class="bg-light rounded p-2 text-primary d-flex align-items-center justify-content-center shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <rect x="1" y="3" width="14" height="10" rx="1.5" />
                                            <path d="M1 5l7 5 7-5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-uppercase text-muted fw-semibold"
                                            style="font-size: 0.7rem; letter-spacing: 0.05em;">Email</div>
                                        <div class="text-dark" style="font-size: 0.875rem;">
                                            <a href="mailto:{{ $lead->email }}"
                                                class="text-decoration-none">{{ $lead->email }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($lead->phone)
                                <div class="d-flex align-items-start gap-3">
                                    <div
                                        class="bg-light rounded p-2 text-success d-flex align-items-center justify-content-center shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path
                                                d="M2 2h3l1.5 4-2 1.5a11 11 0 004 4L10 9.5l4 1.5v3a1 1 0 01-1 1A14 14 0 012 3a1 1 0 011-1z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-uppercase text-muted fw-semibold"
                                            style="font-size: 0.7rem; letter-spacing: 0.05em;">Phone</div>
                                        <div class="text-dark" style="font-size: 0.875rem;">
                                            <a href="tel:{{ $lead->phone }}"
                                                class="text-decoration-none text-dark">{{ $lead->phone }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($lead->expected_value)
                                <div class="d-flex align-items-start gap-3">
                                    <div
                                        class="bg-light rounded p-2 text-warning d-flex align-items-center justify-content-center shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <circle cx="8" cy="8" r="6.5" />
                                            <path
                                                d="M8 4.5v7M5.5 6.5c0-1.1.9-2 2.5-2s2.5.9 2.5 2c0 2.5-5 2.5-5 5 0 1.1.9 2 2.5 2s2.5-.9 2.5-2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-uppercase text-muted fw-semibold"
                                            style="font-size: 0.7rem; letter-spacing: 0.05em;">Expected Value</div>
                                        <div class="text-success fw-bold" style="font-size: 0.95rem;">
                                            PHP {{ number_format($lead->expected_value, 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($lead->source)
                                <div class="d-flex align-items-start gap-3">
                                    <div
                                        class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center flex-shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <circle cx="13" cy="3" r="1.5" />
                                            <circle cx="13" cy="13" r="1.5" />
                                            <circle cx="3" cy="8" r="1.5" />
                                            <path d="M4.5 8h6.5M11.5 4l-6.5 3M11.5 12L5 9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-uppercase text-muted fw-semibold"
                                            style="font-size: 0.7rem; letter-spacing: 0.05em;">Source</div>
                                        <div class="text-dark" style="font-size: 0.875rem;">{{ $lead->source }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($lead->assignedUser)
                                <div class="d-flex align-items-start gap-3">
                                    <div
                                        class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center flex-shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <circle cx="8" cy="5.5" r="3" />
                                            <path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-uppercase text-muted fw-semibold"
                                            style="font-size: 0.7rem; letter-spacing: 0.05em;">Assigned To</div>
                                        <div class="text-dark" style="font-size: 0.875rem;">
                                            {{ $lead->assignedUser->name }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-start gap-3">
                                <div
                                    class="bg-light rounded p-2 text-secondary d-flex align-items-center justify-content-center flex-shrink-0">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <rect x="1.5" y="2.5" width="13" height="12" rx="1.5" />
                                        <path d="M5 1.5v2M11 1.5v2M1.5 6.5h13" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-uppercase text-muted fw-semibold"
                                        style="font-size: 0.7rem; letter-spacing: 0.05em;">Created</div>
                                    <div class="text-dark" style="font-size: 0.875rem;">
                                        {{ $lead->created_at->format('M d, Y') }}
                                        <span
                                            class="text-muted small ms-1">({{ $lead->created_at->diffForHumans() }})</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Notes card --}}
                @if ($lead->notes)
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2 text-uppercase text-muted fw-bold"
                            style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M3 2h8l2 2v10a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z" />
                                <path d="M5 7h6M5 10h4" />
                            </svg>
                            Notes
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-secondary"
                                style="font-size: 0.875rem; white-space: pre-line; line-height: 1.6;">{{ $lead->notes }}
                            </p>
                        </div>
                    </div>
                @endif

            </div>

            {{-- RIGHT: Pipeline + Activity  --}}
            <div class="col-12 col-lg-8">

                {{-- Pipeline card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2 text-uppercase text-muted fw-bold"
                        style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <path d="M1 5h14M1 11h14M5 2v12M11 2v12" />
                        </svg>
                        Pipeline Stage
                    </div>
                    <div class="card-body p-4">

                        {{-- Step tracker utilizing flexbox --}}
                        <div class="d-flex align-items-start justify-content-between mb-4 position-relative px-2">
                            @php
                                $pipelineSteps = [
                                    'New',
                                    'Contacted',
                                    'Qualified',
                                    'Proposal Sent',
                                    'Negotiation',
                                    'Won',
                                ];
                                $activeIndex = array_search(ucfirst($lead->status), $pipelineSteps);
                                $isLost = strtolower($lead->status) === 'lost';
                            @endphp

                            @foreach ($pipelineSteps as $i => $step)
                                @php
                                    $isDone = !$isLost && $activeIndex !== false && $i < $activeIndex;
                                    $isActive = !$isLost && $activeIndex === $i;
                                    $isError = $isLost;
                                @endphp

                                <div class="d-flex flex-column align-items-center text-center z-1" style="flex: 1;">
                                    {{-- Circle Dot --}}
                                    <div class="rounded-circle mb-2"
                                        style="width: 12px; height: 12px; transition: all 0.2s; 
                                    background-color: {{ $isDone ? '#198754' : ($isActive ? '#212529' : ($isError ? '#dc3545' : '#dee2e6')) }}; 
                                    box-shadow: {{ $isActive ? '0 0 0 4px rgba(33, 37, 41, 0.15)' : 'none' }}">
                                    </div>

                                    {{-- Label (hidden on extra small screens) --}}
                                    <div class="d-none d-sm-block small text-nowrap fw-medium"
                                        style="font-size: 0.7rem; color: {{ $isDone ? '#198754' : ($isActive ? '#212529' : ($isError ? '#dc3545' : '#6c757d')) }}">
                                        {{ $step }}
                                    </div>
                                </div>

                                @if ($i < count($pipelineSteps) - 1)
                                    {{-- Connector Line --}}
                                    <div class="flex-grow-1 border-top border-2 mt-1 mx-n2 z-0"
                                        style="border-color: {{ $isDone || ($isActive && !$isLost) ? '#198754' : '#dee2e6' }} !important; transform: translateY(4px);">
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Quick stats --}}
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-1"
                                        style="font-size: 0.65rem; letter-spacing: 0.05em;">Days Active</div>
                                    <div class="font-serif fs-4 text-dark lh-1">{{ $lead->created_at->diffInDays(now()) }}
                                    </div>
                                </div>
                            </div>
                            @if ($lead->expected_value)
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="bg-light rounded-3 p-3 h-100">
                                        <div class="text-uppercase text-muted fw-bold mb-1"
                                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Expected Value</div>
                                        <div class="font-serif fs-5 text-success lh-1 mt-2">
                                            ₱{{ number_format($lead->expected_value / 1000, 0) }}K
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($lead->activities_count ?? false)
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="bg-light rounded-3 p-3 h-100">
                                        <div class="text-uppercase text-muted fw-bold mb-1"
                                            style="font-size: 0.65rem; letter-spacing: 0.05em;">Interactions</div>
                                        <div class="font-serif fs-4 text-dark lh-1">{{ $lead->activities_count }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="bg-light rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-1"
                                        style="font-size: 0.65rem; letter-spacing: 0.05em;">Priority</div>
                                    <div class="fs-6 fw-bold text-dark lh-1 mt-2">{{ ucfirst($lead->priority) }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Activity card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2 text-uppercase text-muted fw-bold"
                        style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <circle cx="8" cy="8" r="6.5" />
                            <path d="M8 5v3l2 2" />
                        </svg>
                        Activity
                    </div>
                    <div class="card-body p-4">

                        @if (isset($activities) && $activities->count())
                            <div class="d-flex flex-column gap-3 mb-4">
                                {{-- BUG FIX: Changed $loop to $activity --}}
                                @foreach ($activities as $activity)
                                    <div class="d-flex gap-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                                        @php
                                            $aType = $activity->type ?? '';
                                            $iconBg = 'bg-light';
                                            $iconColor = 'text-secondary';

                                            if ($aType === 'email') {
                                                $iconBg = 'bg-primary bg-opacity-10';
                                                $iconColor = 'text-primary';
                                            } elseif ($aType === 'call') {
                                                $iconBg = 'bg-warning bg-opacity-10';
                                                $iconColor = 'text-warning';
                                            } elseif ($aType === 'status') {
                                                $iconBg = 'bg-success bg-opacity-10';
                                                $iconColor = 'text-success';
                                            }
                                        @endphp

                                        <div class="rounded p-2 d-flex align-items-center justify-content-center flex-shrink-0 {{ $iconBg }} {{ $iconColor }}"
                                            style="width: 36px; height: 36px;">
                                            @if ($aType === 'email')
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <rect x="1" y="3" width="14" height="10" rx="1.5" />
                                                    <path d="M1 5l7 5 7-5" />
                                                </svg>
                                            @elseif($aType === 'call')
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <path
                                                        d="M2 2h3l1.5 4-2 1.5a11 11 0 004 4L10 9.5l4 1.5v3a1 1 0 01-1 1A14 14 0 012 3a1 1 0 011-1z" />
                                                </svg>
                                            @elseif($aType === 'status')
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <path d="M13 5L6 12l-3-3" />
                                                </svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <circle cx="8" cy="8" r="6.5" />
                                                    <path d="M8 5v3l2 2" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-dark" style="font-size: 0.875rem; line-height: 1.5;">
                                                {{ $activity->description }}</div>
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                {{ $activity->created_at->format('M d, Y · g:i A') }}
                                                @if ($activity->user)
                                                    · {{ $activity->user->name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-4">No activity recorded yet.</p>
                        @endif

                        {{-- Log note form --}}
                        @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                            <div class="d-flex align-items-start gap-2 pt-3 border-top">
                                <textarea class="form-control bg-light border-0 shadow-none" rows="2"
                                    placeholder="Log a call, note, or update…" id="activityNote" style="resize: none; font-size: 0.875rem;"></textarea>
                                <button class="btn btn-dark px-3 fw-medium" type="button"
                                    style="font-size: 0.875rem;">Add</button>
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Delete confirmation modal ───────────────────────── --}}
    @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
        <form id="deleteLeadForm" action="{{ route('leads.destroy', $lead) }}" method="POST" class="d-none">
            @csrf @method('DELETE')
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        // Delete confirmation
        document.addEventListener('DOMContentLoaded', () => {
            const deleteBtn = document.getElementById('deleteLeadBtn');
            const deleteForm = document.getElementById('deleteLeadForm');
            if (deleteBtn && deleteForm) {
                deleteBtn.addEventListener('click', () => {
                    if (confirm('Delete this lead? This action cannot be undone.')) {
                        deleteForm.submit();
                    }
                });
            }
        });
    </script>
@endpush