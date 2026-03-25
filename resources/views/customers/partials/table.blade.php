<div class="card border-0 shadow-sm">

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm">

        {{-- Toolbar --}}
        <div class="card-header bg-white border-bottom p-3">
            <form action="{{ route('customers.index') }}" method="GET" id="filterForm"
                class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                <div class="position-relative" style="max-width: 300px; flex: 1;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm ps-5"
                        placeholder="Search name, email, phone…" value="{{ request('search') }}" autocomplete="off">
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <select name="status" id="statusFilter" class="form-select form-select-sm w-auto"
                        style="min-width: 120px;">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="ms-lg-auto d-flex gap-2">
                    <a href="{{ route('customers.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download"></i> CSV
                    </a>
                    <a href="{{ route('customers.index', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-pdf"></i> PDF
                    </a>
                </div>

            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="customerTable">
                <thead class="table-light">
                    <tr>
                        <th class="small text-muted py-3" style="width: 50px;">#</th>
                        <th class="small text-muted py-3" style="min-width: 140px;">Customer</th>
                        <th class="small text-muted py-3" style="min-width: 200px;">Email</th>
                        <th class="small text-muted py-3 d-none d-sm-table-cell" style="min-width: 140px;">Phone</th>
                        <th class="small text-muted py-3 d-none d-lg-table-cell" style="min-width: 250px;">Address</th>
                        <th class="small text-muted py-3" style="min-width: 110px;">Status</th>
                        <th class="small text-muted py-3 d-none d-md-table-cell" style="min-width: 120px;">Created</th>
                        <th class="small text-muted py-3" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    @forelse ($customers as $index => $customer)
                        <tr>
                            <td class="small text-muted py-3">{{ $customers->firstItem() + $index }}</td>
                            <td class="small text-muted py-3">{{ $customer->first_name }} {{ $customer->last_name }}
                            </td>
                            <td class="small text-muted text-break py-3">{{ $customer->email }}</td>
                            <td class="small text-muted py-3 d-none d-sm-table-cell">{{ $customer->phone }}</td>
                            <td class="small text-muted py-3 d-none d-lg-table-cell">{{ $customer->address }}</td>
                            <td class="py-3">
                                <x-status-badge :status="$customer->status" />
                            </td>
                            <td class="small text-muted py-3 d-none d-md-table-cell">
                                {{ $customer->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3">
                                <div class="d-flex gap-2">
                                    <button type="button"
                                        class="btn btn-sm btn-light border text-primary d-flex align-items-center justify-content-center"
                                        style="width: 36px; height: 36px;" data-bs-toggle="modal"
                                        data-bs-target="#viewModal" onclick="viewCustomer({{ $customer->id }})"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('customers.index', ['edit' => $customer->id]) }}"
                                        class="btn btn-sm btn-light border text-dark d-flex align-items-center justify-content-center"
                                        style="width: 36px; height: 36px;" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('customers.index', ['delete' => $customer->id]) }}"
                                        class="btn btn-sm btn-light border text-danger d-flex align-items-center justify-content-center"
                                        style="width: 36px; height: 36px;" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-3">
            {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>

    </div>
    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const search = document.getElementById('searchInput');
            const status = document.getElementById('statusFilter');
            let debounceTimer;

            // Auto-submit after user stops typing (400ms delay)
            search.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), 400);
            });

            // Instant submit on status change
            status.addEventListener('change', function() {
                form.submit();
            });
        })();
    </script>
