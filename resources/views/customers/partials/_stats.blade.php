@php
    $stats = [
        ['label' => 'Total Customers', 'value' => $customers->total(), 'color' => '#1d4ed8', 'icon' => 'bi-people'],
        ['label' => 'Active', 'value' => $customerIsActive, 'color' => '#16a34a', 'icon' => 'bi-check-circle'],
        ['label' => 'Inactive', 'value' => $customerIsInactive, 'color' => '#e11d48', 'icon' => 'bi-x-circle'],
        ['label' => 'New This Month', 'value' => $customerThisMonth, 'color' => '#0369a1', 'icon' => 'bi-calendar'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($stats as $stat)
        <div class="col-6 col-lg-3">
            <div class="card h-100 border shadow-sm bg-white">
                <div class="card-body px-3 py-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small text-uppercase fw-medium text-truncate"
                            style="color: #9ca3af; letter-spacing: 0.06em; font-size: 0.7rem;">
                            {{ $stat['label'] }}
                        </div>
                        <i class="bi {{ $stat['icon'] }} text-muted" style="font-size: 0.95rem; opacity: 0.5;"></i>
                    </div>
                    <div class="fw-bold lh-1" style="font-size: 2rem; color: {{ $stat['color'] }};">
                        {{ $stat['value'] }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
