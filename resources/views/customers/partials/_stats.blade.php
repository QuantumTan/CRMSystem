@php
    $stats = [
        ['label' => 'Total Customers', 'value' => $customers->total(), 'badge' => 'All Customers', 'badgeClass' => 'bg-primary-subtle text-primary'],
        ['label' => 'Active Customers', 'value' => $customerIsActive, 'badge' => 'Active', 'badgeClass' => 'bg-success-subtle text-success'],
        ['label' => 'Inactive Customers', 'value' => $customerIsInactive, 'badge' => 'Inactive', 'badgeClass' => 'bg-danger-subtle text-danger'],
        ['label' => 'New This Month', 'value' => $customerThisMonth, 'badge' => 'New', 'badgeClass' => 'bg-info-subtle text-info'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($stats as $stat)
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 crm-report-stat-card">
                <div class="card-body">
                    <div class="crm-report-stat-label">{{ $stat['label'] }}</div>
                    <div class="d-flex align-items-end justify-content-between gap-3">
                        <h3 class="crm-report-stat-value mb-0">{{ number_format($stat['value']) }}</h3>
                        <span class="badge {{ $stat['badgeClass'] }}">{{ $stat['badge'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
