@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $activeFilters = $filters ?? ['from' => request('from'), 'to' => request('to')];
        $hasDateFilter = filled($activeFilters['from'] ?? null) || filled($activeFilters['to'] ?? null);
        $exportQuery = array_filter([
            'from' => $activeFilters['from'] ?? null,
            'to' => $activeFilters['to'] ?? null,
        ]);
    @endphp

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="mb-1 fs-3 fw-semibold">Reports</h2>
                <p class="text-muted mb-0 small">
                    Team and pipeline reporting for admin and managers.
                    @if ($hasDateFilter)
                        <span class="d-inline-block ms-1">
                            Filtered from {{ $activeFilters['from'] ?: 'Start' }} to {{ $activeFilters['to'] ?: 'Now' }}.
                        </span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.export.csv', $exportQuery) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-filetype-csv me-1"></i>Export CSV
                </a>
                <a href="{{ route('reports.export.pdf', $exportQuery) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-filetype-pdf me-1"></i>Export PDF
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 crm-toolkit">
            <div class="card-body">
                <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="from" class="form-label mb-1">From Date</label>
                        <input
                            type="date"
                            id="from"
                            name="from"
                            value="{{ $activeFilters['from'] }}"
                            class="form-control @error('from') is-invalid @enderror"
                        >
                        @error('from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="to" class="form-label mb-1">To Date</label>
                        <input
                            type="date"
                            id="to"
                            name="to"
                            value="{{ $activeFilters['to'] }}"
                            class="form-control @error('to') is-invalid @enderror"
                        >
                        @error('to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Apply Filter
                        </button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Total Customers</div>
                        <div class="d-flex align-items-end justify-content-between">
                            <h3 class="mb-0">{{ number_format($data['totalCustomers']) }}</h3>
                            <span class="badge bg-primary-subtle text-primary">All Customers</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Pipeline Leads</div>
                        <div class="d-flex align-items-end justify-content-between">
                            <h3 class="mb-0">{{ number_format($data['salesPipelineSummary']['active_pipeline_leads']) }}</h3>
                            <span class="badge bg-warning-subtle text-warning">Active</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Won Leads</div>
                        <div class="d-flex align-items-end justify-content-between">
                            <h3 class="mb-0">{{ number_format($data['salesPipelineSummary']['won_leads']) }}</h3>
                            <span class="badge bg-success-subtle text-success">Closed Won</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Follow-up Completion</div>
                        <div class="d-flex align-items-end justify-content-between">
                            <h3 class="mb-0">{{ $data['followUpCompletion']['completion_rate'] }}%</h3>
                            <span class="badge bg-info-subtle text-info">Completion Rate</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1">Lead Status Report</h5>
                        <p class="text-muted small mb-0">Distribution across each lead stage.</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="height: 280px;">
                            <canvas id="leadStatusChart" aria-label="Lead status chart" role="img"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0 crm-data-table-compact">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['leadsByStatus'] as $row)
                                        <tr>
                                            <td>{{ str($row['status'])->replace('_', ' ')->title() }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">No lead data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1">Sales Pipeline Summary</h5>
                        <p class="text-muted small mb-0">Pipeline volume and expected deal value snapshot.</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="height: 280px;">
                            <canvas id="pipelineSummaryChart" aria-label="Pipeline summary chart" role="img"></canvas>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-light">
                                    <div class="small text-muted">Total Leads</div>
                                    <div class="fs-5 fw-semibold">{{ number_format($data['salesPipelineSummary']['total_leads']) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-light">
                                    <div class="small text-muted">Lost Leads</div>
                                    <div class="fs-5 fw-semibold">{{ number_format($data['salesPipelineSummary']['lost_leads']) }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3 border">
                                    <div class="small text-muted">Total Expected Value</div>
                                    <div class="fs-5 fw-semibold">Php {{ number_format($data['salesPipelineSummary']['total_expected_value'], 2) }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3 border">
                                    <div class="small text-muted">Active Pipeline Value</div>
                                    <div class="fs-5 fw-semibold">Php {{ number_format($data['salesPipelineSummary']['active_expected_value'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1">User Activity Report</h5>
                        <p class="text-muted small mb-0">Activity volume by system user.</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="height: 300px;">
                            <canvas id="userActivityChart" aria-label="User activity chart" role="img"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 crm-data-table-compact">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th class="text-end">Activities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['userActivity'] as $row)
                                        <tr>
                                            <td class="fw-medium">{{ $row->name }}</td>
                                            <td>{{ str($row->role)->title() }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row->total_activities) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No user activity available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1">Follow-up Completion Report</h5>
                        <p class="text-muted small mb-0">Completion and pending follow-up health.</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" style="height: 280px;">
                            <canvas id="followUpChart" aria-label="Follow-up completion chart" role="img"></canvas>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 rounded-3 bg-success-subtle">
                                    <div class="small text-success-emphasis">Completed</div>
                                    <div class="fs-5 fw-semibold text-success-emphasis">
                                        {{ number_format($data['followUpCompletion']['completed']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3 bg-warning-subtle">
                                    <div class="small text-warning-emphasis">Pending</div>
                                    <div class="fs-5 fw-semibold text-warning-emphasis">
                                        {{ number_format($data['followUpCompletion']['pending']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3 bg-danger-subtle">
                                    <div class="small text-danger-emphasis">Overdue</div>
                                    <div class="fs-5 fw-semibold text-danger-emphasis">
                                        {{ number_format($data['followUpCompletion']['overdue']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const leadStatusRows = @json($data['leadsByStatus']);
            const userActivityRows = @json($data['userActivity']);
            const pipelineSummary = @json($data['salesPipelineSummary']);
            const followUp = @json($data['followUpCompletion']);

            const leadStatusLabels = leadStatusRows.map((item) =>
                item.status.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
            );
            const leadStatusValues = leadStatusRows.map((item) => item.total);

            const userLabels = userActivityRows.map((item) => item.name);
            const userValues = userActivityRows.map((item) => item.total_activities);

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true,
                        },
                    },
                },
            };

            const leadStatusContext = document.getElementById('leadStatusChart');
            if (leadStatusContext && leadStatusValues.length > 0) {
                new Chart(leadStatusContext, {
                    type: 'doughnut',
                    data: {
                        labels: leadStatusLabels,
                        datasets: [{
                            data: leadStatusValues,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1', '#198754', '#fd7e14'],
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        ...commonOptions,
                        cutout: '60%',
                    },
                });
            }

            const pipelineContext = document.getElementById('pipelineSummaryChart');
            if (pipelineContext) {
                new Chart(pipelineContext, {
                    type: 'bar',
                    data: {
                        labels: ['Active', 'Won', 'Lost'],
                        datasets: [{
                            label: 'Leads',
                            data: [
                                pipelineSummary.active_pipeline_leads,
                                pipelineSummary.won_leads,
                                pipelineSummary.lost_leads,
                            ],
                            backgroundColor: ['#0dcaf0', '#198754', '#dc3545'],
                        }],
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: false,
                            },
                        },
                    },
                });
            }

            const userActivityContext = document.getElementById('userActivityChart');
            if (userActivityContext && userValues.length > 0) {
                new Chart(userActivityContext, {
                    type: 'bar',
                    data: {
                        labels: userLabels,
                        datasets: [{
                            label: 'Activities',
                            data: userValues,
                            backgroundColor: '#0d6efd',
                            borderRadius: 6,
                            maxBarThickness: 36,
                        }],
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                display: false,
                            },
                        },
                    },
                });
            }

            const followUpContext = document.getElementById('followUpChart');
            if (followUpContext) {
                new Chart(followUpContext, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'Pending', 'Overdue'],
                        datasets: [{
                            data: [followUp.completed, followUp.pending, followUp.overdue],
                            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        ...commonOptions,
                        cutout: '62%',
                    },
                });
            }
        })();
    </script>
@endpush
