@php
    $scope = $data['scope'] ?? 'sales';

    $scopeTitle = match ($scope) {
        'admin' => 'Organization Overview',
        'manager' => 'Team Performance Snapshot',
        default => 'My Work Snapshot',
    };

    $scopeDescription = match ($scope) {
        'admin' => 'Cross-team customer, lead, follow-up, and activity performance.',
        'manager' => 'Monitor lead progress and follow-up execution across your team.',
        default => 'Assigned leads, follow-ups, and latest interactions that need your attention.',
    };

    $statusLabels = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    $upcomingPendingFollowUps = max(($data['pendingFollowUps'] ?? 0) - ($data['overdueFollowUps'] ?? 0), 0);
@endphp

<div class="crm-page-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="crm-eyebrow mb-2">Dashboard Overview</div>
        <h2 class="h4 mb-1">{{ $scopeTitle }}</h2>
        <p class="text-muted mb-0">{{ $scopeDescription }}</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-person-lines-fill me-1"></i> Customers
        </a>
        <a href="{{ route('leads.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-send me-1"></i> Leads
        </a>
        <a href="{{ route('follow-ups.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-check2-all me-1"></i> Follow-ups
        </a>
        @if (auth()->user()?->hasAnyRole('admin', 'manager'))
            <a href="{{ route('reports.index') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-graph-up-arrow me-1"></i> Reports
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-stat-card">
            <div class="card-body">
                <div class="crm-stat-label">Total Customers</div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h3 class="crm-stat-value mb-0">{{ number_format($data['totalCustomers']) }}</h3>
                    <div class="crm-stat-icon text-primary">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-stat-card">
            <div class="card-body">
                <div class="crm-stat-label">Active Leads</div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h3 class="crm-stat-value mb-0">{{ number_format($data['totalActiveLeads']) }}</h3>
                    <div class="crm-stat-icon text-warning">
                        <i class="bi bi-funnel fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-stat-card">
            <div class="card-body">
                <div class="crm-stat-label">Completed Follow-ups</div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h3 class="crm-stat-value mb-0">{{ number_format($data['completedFollowUps']) }}</h3>
                    <div class="crm-stat-icon text-success">
                        <i class="bi bi-check2-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-stat-card">
            <div class="card-body">
                <div class="crm-stat-label">Overdue Follow-ups</div>
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <h3 class="crm-stat-value mb-0">{{ number_format($data['overdueFollowUps']) }}</h3>
                    <div class="crm-stat-icon text-danger">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h4 class="h6 mb-0">Lead Pipeline Chart</h4>
            </div>
            <div class="card-body pt-3">
                <canvas id="leadStatusChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h4 class="h6 mb-0">Follow-up Health Chart</h4>
            </div>
            <div class="card-body pt-3">
                <canvas id="followUpChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h4 class="h6 mb-0">Lead Status Breakdown</h4>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table crm-table crm-table-compact table-hover align-middle mb-0 crm-data-table-compact crm-table-fixed">
                        <colgroup>
                            <col style="width: 70%;">
                            <col style="width: 30%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['leadStatusCounts'] as $status => $count)
                                @php $leadStatusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)); @endphp
                                <tr>
                                    <td class="crm-table-cell-truncate" title="{{ $leadStatusLabel }}">{{ $leadStatusLabel }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($count) }}</td>
                                </tr>
                            @endforeach
                            <tr class="crm-table-summary-row">
                                <td>Total Leads</td>
                                <td class="text-end">{{ number_format($data['totalLeads']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="h6 mb-0">Upcoming Follow-ups</h4>
                <span class="badge text-bg-light">Pending: {{ number_format($data['pendingFollowUps']) }}</span>
            </div>
            <div class="card-body pt-3">
                @if ($data['upcomingFollowUps']->isEmpty())
                    <p class="text-muted mb-0">No upcoming follow-ups.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($data['upcomingFollowUps'] as $followUp)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $followUp->title }}</div>
                                        <div class="small text-muted">
                                            {{ optional($followUp->due_date)->format('M d, Y') }}
                                            @if ($followUp->user)
                                                • {{ $followUp->user->name }}
                                            @endif
                                        </div>
                                        <div class="small text-muted mt-1">
                                            @if ($followUp->customer)
                                                Customer: {{ $followUp->customer->first_name }} {{ $followUp->customer->last_name }}
                                            @elseif ($followUp->lead)
                                                Lead: {{ $followUp->lead->name }}
                                            @else
                                                Unlinked follow-up
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge text-bg-warning">Pending</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h4 class="h6 mb-0">Recent Activities</h4>
            </div>
            <div class="card-body pt-3">
                @if ($data['recentActivities']->isEmpty())
                    <p class="text-muted mb-0">No recent activities.</p>
                @else
                    <div class="table-responsive">
                        <table class="table crm-table crm-table-compact table-hover align-middle mb-0 crm-data-table-compact crm-table-fixed crm-activity-table">
                            <colgroup>
                                <col class="crm-col-activity-date">
                                <col class="crm-col-activity-type">
                                <col class="crm-col-activity-description">
                                <col class="crm-col-activity-related">
                                <col class="crm-col-activity-user">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Related To</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['recentActivities'] as $activity)
                                    @php
                                        $activityDate = optional($activity->activity_date)->format('M d, Y') ?: 'N/A';
                                        $activityDescription = $activity->description ? preg_replace('/\s+/', ' ', trim((string) $activity->description)) : 'N/A';
                                        $activityRelated = match (true) {
                                            (bool) $activity->customer => 'Customer: '.trim($activity->customer->first_name.' '.$activity->customer->last_name),
                                            (bool) $activity->lead => 'Lead: '.$activity->lead->name,
                                            default => 'N/A',
                                        };
                                        $activityUser = $activity->user?->name ?? 'N/A';
                                    @endphp
                                    <tr>
                                        <td class="crm-table-cell-truncate" title="{{ $activityDate }}">{{ $activityDate }}</td>
                                        <td class="crm-table-cell-truncate" title="{{ ucfirst($activity->activity_type) }}">{{ ucfirst($activity->activity_type) }}</td>
                                        <td class="crm-table-cell-truncate" title="{{ $activityDescription }}">{{ $activityDescription }}</td>
                                        <td class="crm-table-cell-truncate" title="{{ $activityRelated }}">{{ $activityRelated }}</td>
                                        <td class="crm-table-cell-truncate" title="{{ $activityUser }}">{{ $activityUser }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            (() => {
                const leadStatusLabels = @json(collect($data['leadStatusCounts'])->keys()->map(fn ($status) => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)))->values());
                const leadStatusValues = @json(collect($data['leadStatusCounts'])->values());

                const leadStatusChartContext = document.getElementById('leadStatusChart');

                if (leadStatusChartContext) {
                    new Chart(leadStatusChartContext, {
                        type: 'doughnut',
                        data: {
                            labels: leadStatusLabels,
                            datasets: [{
                                data: leadStatusValues,
                                backgroundColor: ['#0d6efd', '#6f42c1', '#198754', '#fd7e14', '#ffc107', '#20c997', '#dc3545'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                            },
                        },
                    });
                }

                const followUpChartContext = document.getElementById('followUpChart');

                if (followUpChartContext) {
                    new Chart(followUpChartContext, {
                        type: 'bar',
                        data: {
                            labels: ['Completed', 'Pending (Upcoming)', 'Overdue'],
                            datasets: [{
                                data: [
                                    {{ (int) ($data['completedFollowUps'] ?? 0) }},
                                    {{ (int) $upcomingPendingFollowUps }},
                                    {{ (int) ($data['overdueFollowUps'] ?? 0) }},
                                ],
                                backgroundColor: ['#198754', '#0dcaf0', '#dc3545'],
                                borderRadius: 8,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0,
                                    },
                                },
                            },
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                        },
                    });
                }
            })();
        </script>
    @endpush
@endonce
