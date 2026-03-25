@php
    $stats = [
        ['label' => 'Total Customers', 'value' => $customers->total()],
        ['label' => 'Active', 'value' => $customerIsActive],
        ['label' => 'Inactive', 'value' => $customerIsInactive],
        ['label' => 'New This Month', 'value' => $customerThisMonth],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($stats as $stat)
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body px-3 py-3">
                    <div class="text-muted small text-uppercase mb-1 text-truncate">{{ $stat['label'] }}</div>
                    <div class="h3 fw-semibold mb-1">{{ $stat['value'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
