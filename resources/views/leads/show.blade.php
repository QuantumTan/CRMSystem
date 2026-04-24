@extends('layouts.app')

@section('title', $lead->name)

@section('content')
    @php
        $user = auth()->user();
        $statusClass = match (strtolower((string) $lead->status)) {
            'new' => 'crm-table-status crm-table-status-primary',
            'contacted' => 'crm-table-status crm-table-status-info',
            'qualified', 'won' => 'crm-table-status crm-table-status-success',
            'proposal_sent', 'proposal sent', 'negotiation' => 'crm-table-status crm-table-status-warning',
            'lost' => 'crm-table-status crm-table-status-danger',
            default => 'crm-table-status crm-table-status-muted',
        };
        $priorityClass = match (strtolower((string) $lead->priority)) {
            'high', 'critical' => 'crm-table-status crm-table-status-danger',
            'medium' => 'crm-table-status crm-table-status-warning',
            'low' => 'crm-table-status crm-table-status-success',
            default => 'crm-table-status crm-table-status-muted',
        };
        $currencyCode = $systemConfiguration?->currency_code ?? config('crm.currency_code', 'PHP');
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="crm-page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <div class="crm-eyebrow mb-2">Lead View</div>
                <h1 class="h3 mb-1 fw-semibold">{{ $lead->name }}</h1>
                <p class="text-muted mb-0 small">Review pipeline status, contact details, value, ownership, and recent activity.</p>
            </div>
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="row g-4 crm-record-layout">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm crm-hero-card mb-4">
                    <div class="card-body p-4 p-lg-5 crm-hero-body">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="crm-hero-avatar">
                                    {{ strtoupper(substr($lead->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-muted small mb-1">{{ $lead->lead_id }}</div>
                                    <h2 class="h4 mb-1">{{ $lead->name }}</h2>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                                        <span class="{{ $priorityClass }}">{{ ucfirst($lead->priority) }} Priority</span>
                                    </div>
                                </div>
                            </div>

                            @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                                <div class="crm-action-cluster">
                                    @if ($lead->isWon() && ! $lead->isConverted())
                                        <form action="{{ route('leads.convert', $lead) }}" method="POST" onsubmit="return confirm('Convert this lead into a customer?');">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Convert to Customer</button>
                                        </form>
                                    @elseif($lead->convertedToCustomer)
                                        <a href="{{ route('customers.show', $lead->convertedToCustomer) }}" class="btn btn-outline-primary">View Customer</a>
                                    @endif

                                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-primary">Edit Lead</a>

                                    @can('delete', $lead)
                                        @if (! $lead->isConverted())
                                            @include('leads.partials._delete-trigger', [
                                                'lead' => $lead,
                                                'buttonClass' => 'btn btn-outline-danger d-inline-flex align-items-center gap-2',
                                            ])
                                        @endif
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm crm-detail-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Lead Information</h2>
                            <p class="crm-form-section-copy">Key contact, source, and value data for this opportunity.</p>
                        </div>

                        <div class="crm-detail-grid crm-detail-grid-3">
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Email</span>
                                <div class="crm-detail-value">{{ $lead->email ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Phone</span>
                                <div class="crm-detail-value">{{ $lead->phone ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Source</span>
                                <div class="crm-detail-value">{{ $lead->source ?: 'N/A' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Expected Value</span>
                                <div class="crm-detail-value">
                                    {{ $lead->expected_value ? $currencyCode.' '.number_format($lead->expected_value, 2) : 'N/A' }}
                                </div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Assigned User</span>
                                <div class="crm-detail-value">{{ $lead->assignedUser?->name ?: 'Unassigned' }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Created</span>
                                <div class="crm-detail-value">{{ $lead->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($lead->notes)
                    <div class="card border-0 shadow-sm crm-detail-card mb-4">
                        <div class="card-body">
                            <div class="crm-form-section-head">
                                <h2 class="crm-form-section-title">Notes</h2>
                                <p class="crm-form-section-copy">Additional sales context saved for this opportunity.</p>
                            </div>
                            <div class="crm-note-box" style="white-space: pre-line;">{{ $lead->notes }}</div>
                        </div>
                    </div>
                @endif

                @can('create', \App\Models\Activity::class)
                    @include('activities._form', ['activity' => null, 'lead' => $lead, 'customer' => null])
                @endcan

                @include('activities._timeline', ['activities' => $activities])
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm crm-detail-card mb-4">
                    <div class="card-body">
                        <div class="crm-form-section-head">
                            <h2 class="crm-form-section-title">Pipeline Summary</h2>
                            <p class="crm-form-section-copy">Quick view of stage, conversion, and lifecycle timing.</p>
                        </div>

                        <div class="crm-detail-grid">
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Current Status</span>
                                <div class="crm-detail-value">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Priority</span>
                                <div class="crm-detail-value">{{ ucfirst($lead->priority) }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Days Active</span>
                                <div class="crm-detail-value">{{ $lead->created_at->diffInDays(now()) }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Activity Count</span>
                                <div class="crm-detail-value">{{ $activities->count() }}</div>
                            </div>
                            <div class="crm-detail-item">
                                <span class="crm-detail-label">Converted</span>
                                <div class="crm-detail-value">{{ $lead->isConverted() ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($user && ($user->hasRole('admin') || $user->hasRole('sales')))
                    <div class="card border-0 shadow-sm crm-form-card mb-4">
                        <div class="card-body">
                            <div class="crm-form-section-head">
                                <h2 class="crm-form-section-title">Lifecycle Actions</h2>
                                <p class="crm-form-section-copy">Move this lead forward, reopen it, or mark it as lost when needed.</p>
                            </div>

                            <div class="crm-form-actions">
                                @if (! $lead->isLost())
                                    <a href="{{ route('leads.lost-form', $lead) }}" class="btn btn-outline-danger w-100">Mark as Lost</a>
                                @else
                                    <form method="POST" action="{{ route('leads.reopen', $lead) }}" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary w-100">Reopen Lead</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('leads.partials._modal-delete')
@endsection
