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
    $leadStatusTotal = max((int) ($data['totalLeads'] ?? 0), 0);
    $followUpHealthTotal = max(
        (int) ($data['completedFollowUps'] ?? 0) + (int) $upcomingPendingFollowUps + (int) ($data['overdueFollowUps'] ?? 0),
        0
    );
    $activeLeadRate = $leadStatusTotal > 0 ? round(((int) ($data['totalActiveLeads'] ?? 0) / $leadStatusTotal) * 100) : 0;
    $completedFollowUpRate = $followUpHealthTotal > 0 ? round(((int) ($data['completedFollowUps'] ?? 0) / $followUpHealthTotal) * 100) : 0;
    $overdueFollowUpRate = $followUpHealthTotal > 0 ? round(((int) ($data['overdueFollowUps'] ?? 0) / $followUpHealthTotal) * 100) : 0;
    $leadStatusColors = [
        'new' => 'var(--chart-1)',
        'contacted' => 'var(--chart-2)',
        'qualified' => 'var(--chart-3)',
        'proposal_sent' => 'var(--chart-4)',
        'negotiation' => 'var(--chart-5)',
        'won' => 'var(--chart-6)',
        'lost' => 'var(--chart-7)',
    ];
    $wonLeadsCount = (int) ($data['leadStatusCounts']['won'] ?? 0);
    $lostLeadsCount = (int) ($data['leadStatusCounts']['lost'] ?? 0);
    $wonLeadsRate = $leadStatusTotal > 0 ? round(($wonLeadsCount / $leadStatusTotal) * 100) : 0;
    $lostLeadsRate = $leadStatusTotal > 0 ? round(($lostLeadsCount / $leadStatusTotal) * 100) : 0;
    $followUpCompletionRate = $followUpHealthTotal > 0 ? round(((int) ($data['completedFollowUps'] ?? 0) / $followUpHealthTotal) * 100, 1) : 0;
    $followUpCompletionRateLabel = rtrim(rtrim(number_format($followUpCompletionRate, 1), '0'), '.').'%';
@endphp

<div class="crm-dashboard-hero mb-4">
    <div class="crm-dashboard-hero-main">
        <div class="crm-hero-kicker">
            <i class="bi bi-bar-chart-line"></i>
            Dashboard Overview
        </div>
        <h2 class="crm-dashboard-title mb-2">{{ $scopeTitle }}</h2>
        <p class="crm-dashboard-copy mb-0">{{ $scopeDescription }}</p>
    </div>

    <div class="crm-dashboard-actions">
        <div class="crm-dashboard-report-meta">
            <span>Report generated</span>
            <strong>{{ now()->format('M d, Y g:i A') }}</strong>
        </div>
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
        <div class="card border-0 shadow-sm h-100 crm-report-stat-card">
            <div class="card-body">
                <div class="crm-report-stat-label">Total Customers</div>
                <div class="d-flex align-items-end justify-content-between gap-3">
                    <h3 class="crm-report-stat-value mb-0">{{ number_format($data['totalCustomers']) }}</h3>
                    <span class="badge bg-primary-subtle text-primary">All Customers</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-report-stat-card">
            <div class="card-body">
                <div class="crm-report-stat-label">Active Leads</div>
                <div class="d-flex align-items-end justify-content-between gap-3">
                    <h3 class="crm-report-stat-value mb-0">{{ number_format($data['totalActiveLeads']) }}</h3>
                    <span class="badge bg-warning-subtle text-warning">Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-report-stat-card">
            <div class="card-body">
                <div class="crm-report-stat-label">Completed Follow-ups</div>
                <div class="d-flex align-items-end justify-content-between gap-3">
                    <h3 class="crm-report-stat-value mb-0">{{ number_format($data['completedFollowUps']) }}</h3>
                    <span class="badge bg-success-subtle text-success">Completed</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 crm-report-stat-card">
            <div class="card-body">
                <div class="crm-report-stat-label">Overdue Follow-ups</div>
                <div class="d-flex align-items-end justify-content-between gap-3">
                    <h3 class="crm-report-stat-value mb-0">{{ number_format($data['overdueFollowUps']) }}</h3>
                    <span class="badge bg-danger-subtle text-danger">Overdue</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100 crm-dashboard-widget-card crm-pipeline-card">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Pipeline Snapshot</h5>
                        <p class="text-muted small mb-0">Quick stage mix for the dashboard. Full breakdown is in Reports.</p>
                    </div>
                    <div class="crm-card-actions crm-reports-card-actions">
                        <span class="badge bg-primary-subtle text-primary">Total: {{ number_format($leadStatusTotal) }}</span>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-primary btn-sm crm-card-action-btn">
                            <i class="bi bi-arrow-up-right"></i> View Leads
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="crm-pipeline-quick-stats mb-3">
                    <div class="crm-pipeline-stat" style="--pipeline-stat-color: {{ $leadStatusColors['won'] }};">
                        <span class="crm-pipeline-stat-label">Won</span>
                        <strong>{{ number_format($wonLeadsCount) }}</strong>
                        <span>{{ $wonLeadsRate }}% of total leads</span>
                    </div>
                    <div class="crm-pipeline-stat" style="--pipeline-stat-color: {{ $leadStatusColors['lost'] }};">
                        <span class="crm-pipeline-stat-label">Lost</span>
                        <strong>{{ number_format($lostLeadsCount) }}</strong>
                        <span>{{ $lostLeadsRate }}% of total leads</span>
                    </div>
                </div>

                <div class="crm-pipeline-chart-frame mb-3">
                    <canvas id="leadStatusChart" aria-label="Lead pipeline status chart" role="img"></canvas>
                </div>
                <div class="crm-pipeline-legend" aria-label="Lead status data labels">
                    @foreach ($data['leadStatusCounts'] as $status => $count)
                        @php
                            $leadStatusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
                        @endphp
                        <div class="crm-pipeline-legend-pill" style="--label-color: {{ $leadStatusColors[$status] ?? 'var(--chart-10)' }};">
                            <span class="crm-pipeline-legend-swatch"></span>
                            <span class="crm-pipeline-legend-name">{{ $leadStatusLabel }}</span>
                            <strong>{{ number_format($count) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100 crm-dashboard-widget-card">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Follow-up Snapshot</h5>
                        <p class="text-muted small mb-0">Limited health view for immediate action.</p>
                    </div>
                    <div class="crm-card-actions crm-reports-card-actions">
                        <span class="badge bg-primary-subtle text-primary">Total: {{ number_format($followUpHealthTotal) }}</span>
                        @if (auth()->user()?->hasAnyRole('admin', 'manager'))
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm crm-card-action-btn">
                                <i class="bi bi-arrow-up-right"></i> Reports
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="crm-dashboard-chart-frame mb-3">
                    <canvas id="followUpChart" aria-label="Follow-up health chart with data labels" role="img"></canvas>
                </div>
                <div class="crm-dashboard-summary-grid" aria-label="Follow-up health data labels">
                    @php
                        $followUpHealthRows = [
                            ['label' => 'Completed', 'count' => (int) ($data['completedFollowUps'] ?? 0), 'color' => 'var(--chart-1)'],
                            ['label' => 'Pending (Upcoming)', 'count' => (int) $upcomingPendingFollowUps, 'color' => 'var(--chart-2)'],
                            ['label' => 'Overdue', 'count' => (int) ($data['overdueFollowUps'] ?? 0), 'color' => 'var(--chart-3)'],
                        ];
                    @endphp
                    @foreach ($followUpHealthRows as $row)
                        @php $followUpShare = $followUpHealthTotal > 0 ? round(($row['count'] / $followUpHealthTotal) * 100) : 0; @endphp
                        <div class="crm-dashboard-summary-chip" style="--label-color: {{ $row['color'] }};">
                            <span>{{ $row['label'] }}</span>
                            <strong>{{ number_format($row['count']) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100 crm-dashboard-widget-card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Lead Status Preview</h5>
                        <p class="text-muted small mb-0">Limited dashboard preview. Use Reports for detailed tables.</p>
                    </div>
                    @if (auth()->user()?->hasAnyRole('admin', 'manager'))
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm crm-card-action-btn">
                            <i class="bi bi-arrow-up-right"></i> Reports
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @php
                    $leadStatusPreviewRows = collect($data['leadStatusCounts'])->sortDesc();
                    $visibleLeadStatusRows = $leadStatusPreviewRows->take(4);
                    $hiddenLeadStatusRows = $leadStatusPreviewRows->slice(4);
                    $hiddenLeadStatusTotal = $hiddenLeadStatusRows->sum();
                @endphp
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
                            @foreach ($visibleLeadStatusRows as $status => $count)
                                @php $leadStatusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)); @endphp
                                <tr>
                                    <td class="crm-table-cell-truncate" title="{{ $leadStatusLabel }}">{{ $leadStatusLabel }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($count) }}</td>
                                </tr>
                            @endforeach
                            @if ($hiddenLeadStatusRows->isNotEmpty())
                                <tr>
                                    <td class="crm-table-cell-truncate" title="{{ $hiddenLeadStatusRows->keys()->map(fn ($status) => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)))->implode(', ') }}">
                                        Other statuses
                                        <span class="text-muted small">({{ $hiddenLeadStatusRows->count() }})</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($hiddenLeadStatusTotal) }}</td>
                                </tr>
                            @endif
                            <tr class="crm-table-summary-row">
                                <td>Total Leads</td>
                                <td class="text-end">{{ number_format($data['totalLeads']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="crm-dashboard-widget-note mt-3">
                    Showing {{ number_format($visibleLeadStatusRows->count()) }} main statuses
                    @if ($hiddenLeadStatusRows->isNotEmpty())
                        plus {{ number_format($hiddenLeadStatusRows->count()) }} grouped statuses.
                    @else
                        with no hidden statuses.
                    @endif
                    @if (auth()->user()?->hasAnyRole('admin', 'manager'))
                        <a href="{{ route('reports.index') }}">Open full report</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100 crm-report-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-start gap-2">
                <div class="crm-card-title-wrap">
                    <span class="crm-card-icon"><i class="bi bi-calendar2-check"></i></span>
                    <h4 class="h6 mb-1">Upcoming Follow-ups</h4>
                    <p class="text-muted small mb-0">Next pending work ordered by due date.</p>
                </div>
                <span class="crm-report-badge">Pending: {{ number_format($data['pendingFollowUps']) }}</span>
            </div>
            <div class="card-body">
                @if ($data['upcomingFollowUps']->isEmpty())
                    <div class="crm-empty-inline">
                        <i class="bi bi-check2-circle"></i>
                        <span>No upcoming follow-ups.</span>
                    </div>
                @else
                    <div class="crm-followup-list">
                        @foreach ($data['upcomingFollowUps'] as $followUp)
                            <div class="crm-followup-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $followUp->title }}</div>
                                        <div class="small text-muted">
                                            {{ optional($followUp->due_date)->format('M d, Y') }}
                                            @if ($followUp->user)
                                                &bull; {{ $followUp->user->name }}
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
        <div class="card border-0 shadow-sm crm-report-card">
            <div class="card-header border-0">
                <div class="crm-card-title-wrap">
                    <span class="crm-card-icon"><i class="bi bi-clock-history"></i></span>
                    <h4 class="h6 mb-1">Recent Activities</h4>
                    <p class="text-muted small mb-0">Latest recorded customer and lead interactions.</p>
                </div>
            </div>
            <div class="card-body">
                @if ($data['recentActivities']->isEmpty())
                    <div class="crm-empty-inline">
                        <i class="bi bi-inbox"></i>
                        <span>No recent activities.</span>
                    </div>
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
                const chartTheme = getComputedStyle(document.documentElement);
                const resolveThemeColor = (value) => {
                    const match = /^var\((--[\w-]+)\)$/.exec(value);
                    return match ? chartTheme.getPropertyValue(match[1]).trim() : value;
                };
                const chartPalette = Array.from({ length: 10 }, (_, index) => chartTheme.getPropertyValue(`--chart-${index + 1}`).trim());
                const chartTextColor = chartTheme.getPropertyValue('--crm-text').trim() || chartTheme.getPropertyValue('--color-text-heading').trim();
                const chartMutedColor = chartTheme.getPropertyValue('--chart-axis').trim() || chartTheme.getPropertyValue('--color-text-muted').trim();
                const chartSurfaceColor = chartTheme.getPropertyValue('--crm-surface').trim() || chartTheme.getPropertyValue('--color-surface-card').trim();
                const chartBorderColor = chartTheme.getPropertyValue('--chart-grid').trim() || chartTheme.getPropertyValue('--color-border').trim();
                const chartTooltipBg = chartTheme.getPropertyValue('--chart-tooltip-bg').trim();
                const chartTooltipText = chartTheme.getPropertyValue('--chart-tooltip-text').trim();
                const leadStatusColors = @json(collect($data['leadStatusCounts'])->keys()->map(fn ($status) => $leadStatusColors[$status] ?? 'var(--chart-10)')->values()).map(resolveThemeColor);
                const numberFormatter = new Intl.NumberFormat();
                const percentFormatter = new Intl.NumberFormat(undefined, {
                    maximumFractionDigits: 0,
                });

                const getDatasetTotal = (dataset) => dataset.data.reduce((total, value) => total + Number(value || 0), 0);
                const getDataLabel = (value, dataset) => {
                    const numericValue = Number(value || 0);
                    const total = getDatasetTotal(dataset);

                    if (!numericValue) {
                        return '';
                    }

                    const share = total > 0 ? percentFormatter.format((numericValue / total) * 100) : '0';

                    return `${numberFormatter.format(numericValue)} (${share}%)`;
                };
                const getGraphLabel = (value, dataset, chartType) => {
                    const numericValue = Number(value || 0);

                    if (!numericValue) {
                        return '';
                    }

                    if (chartType === 'doughnut') {
                        return numberFormatter.format(numericValue);
                    }

                    return getDataLabel(numericValue, dataset);
                };

                const reportDataLabelPlugin = {
                    id: 'reportDataLabelPlugin',
                    afterDatasetsDraw(chart) {
                        const { ctx } = chart;
                        const chartType = chart.config.type;

                        if (chartType === 'doughnut') {
                            return;
                        }

                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            const meta = chart.getDatasetMeta(datasetIndex);

                            meta.data.forEach((element, index) => {
                                const value = dataset.data[index];
                                const label = getGraphLabel(value, dataset, chartType);

                                if (!label) {
                                    return;
                                }

                                const position = element.tooltipPosition();
                                const isBarChart = chartType === 'bar';
                                const x = position.x;
                                const y = isBarChart ? position.y - 12 : position.y;

                                ctx.save();
                                ctx.font = '600 12px Inter, system-ui, sans-serif';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.lineWidth = 4;
                                ctx.strokeStyle = chartSurfaceColor;
                                ctx.fillStyle = chartTextColor;
                                ctx.strokeText(label, x, y);
                                ctx.fillText(label, x, y);
                                ctx.restore();
                            });
                        });
                    },
                    afterDraw(chart) {
                        if (chart.config.type !== 'doughnut') {
                            return;
                        }

                        const dataset = chart.data.datasets[0];
                        const total = getDatasetTotal(dataset);
                        const { ctx, chartArea } = chart;
                        const centerX = (chartArea.left + chartArea.right) / 2;
                        const centerY = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = chartMutedColor;
                        ctx.font = '700 12px Inter, system-ui, sans-serif';
                        ctx.fillText('Total', centerX, centerY - 18);
                        ctx.fillStyle = chartTextColor;
                        ctx.font = '800 34px Inter, system-ui, sans-serif';
                        ctx.fillText(numberFormatter.format(total), centerX, centerY + 12);
                        ctx.restore();
                    },
                };

                const commonReportPlugins = [reportDataLabelPlugin];

                const leadStatusChartContext = document.getElementById('leadStatusChart');

                if (leadStatusChartContext) {
                    new Chart(leadStatusChartContext, {
                        type: 'doughnut',
                        data: {
                            labels: leadStatusLabels,
                            datasets: [{
                                data: leadStatusValues,
                                backgroundColor: leadStatusColors,
                                borderColor: chartSurfaceColor,
                                borderWidth: 3,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            animation: {
                                animateRotate: true,
                                animateScale: true,
                                duration: 1100,
                                easing: 'easeOutQuart',
                            },
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                        backgroundColor: chartTooltipBg,
                        borderColor: chartBorderColor,
                        borderWidth: 1,
                        bodyColor: chartTooltipText,
                        titleColor: chartTooltipText,
                                    displayColors: true,
                                    padding: 12,
                                    cornerRadius: 10,
                                    callbacks: {
                                        title(context) {
                                            return context[0]?.label || '';
                                        },
                                        label(context) {
                                            return getDataLabel(context.raw, context.dataset);
                                        },
                                    },
                                },
                            },
                        },
                        plugins: commonReportPlugins,
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
                                backgroundColor: chartPalette.slice(0, 3),
                                borderRadius: 10,
                                borderSkipped: false,
                                maxBarThickness: 58,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    grace: '18%',
                                    grid: {
                                        color: chartBorderColor,
                                    },
                                    ticks: {
                                        precision: 0,
                                    },
                                },
                            },
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label(context) {
                                            return `${context.label}: ${getDataLabel(context.raw, context.dataset)}`;
                                        },
                                    },
                                },
                            },
                        },
                        plugins: commonReportPlugins,
                    });
                }
            })();
        </script>
    @endpush
@endonce
