@extends('layouts.admin')

@section('title', 'Customers')

@php
    $isAdmin = auth()->user()?->role === 'admin';
    $isManager = auth()->user()?->role === 'manager';
    $isSales = auth()->user()?->role === 'sales';
@endphp

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- page header --}}
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-semibold">Customers</h4>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                        @if ($isAdmin)
                            Admin
                        @elseif($isManager)
                            Manager
                        @elseif($isSales)
                            Sales Staff
                        @endif
                    </span>
                </div>
                <p class="text-muted mb-0 small">
                    Viewing all customer records
                </p>
            </div>
            <div class="d-grid d-sm-block w-100" style="max-width: max-content;">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <i class="bi bi-plus-lg"></i> Add Customer
                </button>
            </div>
        </div>

        {{-- Stats cards with temp values --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">Total Customers</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="totalCustomers">{{ $customerThisMonth }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">Active</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="activeCustomers">{{ $customerIsActive }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">Inactive</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="inactiveCustomers">{{ $customerIsInactive }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">New This Month</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="newThisMonth">{{ $customerThisMonth }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- customer table card --}}
        <div class="card border-0 shadow-sm">

            {{-- toolbar --}}
            <div class="card-header bg-white border-bottom p-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">

                    {{-- Search Input --}}
                    <div class="position-relative w-100 w-lg-auto" style="flex: 1; max-width: 100%;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control form-control-sm ps-5 w-100"
                            placeholder="Search name, email, phone…" onkeyup="filterTable()">
                    </div>

                    {{-- Filters --}}
                    <div class="d-flex flex-wrap gap-2 w-100 w-lg-auto">
                        <select id="statusFilter" class="form-select form-select-sm flex-grow-1 w-auto"
                            style="min-width: 120px;"">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button class="btn btn-dark btn-sm px-3 flex-grow-1 flex-md-grow-0"
                            onclick="filterTable()">Filter</button>
                        <button class="btn btn-outline-secondary btn-sm flex-grow-1 flex-md-grow-0"">Reset</button>
                    </div>

                    {{-- Export --}}
                    <div class="ms-lg-auto d-flex gap-2 w-100 w-lg-auto justify-content-end mt-2 mt-lg-0">
                        <button class="btn btn-outline-secondary btn-sm flex-grow-1 flex-lg-grow-0"">
                            <i class="bi bi-download"></i> CSV
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-grow-1 flex-lg-grow-0"">
                            <i class="bi bi-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="customerTable">
                    <thead class="table-light">
                        <tr>
                            {{-- Added py-3 for more vertical space and increased min-widths --}}
                            <th class="small text-muted py-3" style="width: 50px;">#</th>
                            <th class="small text-muted py-3" style="min-width: 140px;">Customer</th>
                            <th class="small text-muted py-3" style="min-width: 200px;">Email</th>
                            <th class="small text-muted py-3 d-none d-sm-table-cell" style="min-width: 140px;">Phone</th>
                            <th class="small text-muted py-3 d-none d-lg-table-cell" style="min-width: 250px;">Address</th>
                            <th class="small text-muted py-3" style="min-width: 110px;">Status</th>
                            <th class="small text-muted py-3 d-none d-md-table-cell" style="min-width: 120px;">Created</th>
                            <th class="small text-muted py-3 text-end text-sm-start" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        @forelse ($customers as $index => $customer)
                            <tr>
                                <td class="small text-muted py-3">{{ $customers->firstItem() + $index }}</td>

                                <td class="small text-muted text-wrap py-3">{{ $customer->first_name }}
                                    {{ $customer->last_name }}</td>

                                <td class="small text-muted text-wrap text-break py-3">{{ $customer->email }}</td>

                                <td class="small text-muted text-wrap py-3 d-none d-sm-table-cell">{{ $customer->phone }}
                                </td>

                                <td class="small text-muted text-wrap py-3 d-none d-lg-table-cell">{{ $customer->address }}
                                </td>

                                {{-- badge --}}
                                <td class="py-3">
                                    @if (strtolower($customer->status) === 'active')
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-medium">Active</span>
                                    @else
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-medium">Inactive</span>
                                    @endif
                                </td>

                                <td class="small text-muted text-wrap py-3 d-none d-md-table-cell">
                                    {{ $customer->created_at }}</td>

                                <td class="py-3 text-end text-sm-start">

                                    <div class="d-flex flex-wrap gap-2 justify-content-end justify-content-sm-start">

                                        <button type="button"
                                            class="btn btn-sm btn-light border text-primary d-flex align-items-center justify-content-center"
                                            style="width: 36px; height: 36px;" data-bs-toggle="modal"
                                            data-bs-target="#viewModal" onclick="viewCustomer({{ $customer->id }})"
                                            title="View Details">
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

            {{--  --}}
            <div class="card-footer bg-white border-top py-3">
                {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

    {{-- add edit modal --}}
    @php
        $openModal = $editing || (request('edit') && $errors->any());
    @endphp

    <div class="modal fade {{ $openModal ? 'show' : '' }}" id="customerModal" tabindex="-1"
        style="{{ $openModal ? 'display:block;' : '' }}" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ $editing ? 'Edit Customer' : 'Add Customer' }}</h5>
                    <a href="{{ route('customers.index') }}" class="btn-close"></a>
                </div>

                <form method="POST"
                    action="{{ $editing ? route('customers.update', $editing->id) : route('customers.store') }}">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="first_name"
                                class="form-control @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name', $editing?->first_name) }}" placeholder="Juan" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="last_name"
                                class="form-control @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name', $editing?->last_name) }}" placeholder="dela Cruz" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $editing?->email) }}" placeholder="juan@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $editing?->phone) }}" placeholder="+63 9XX XXX XXXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Address</label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                placeholder="City, Province">{{ old('address', $editing?->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active"
                                    {{ old('status', $editing?->status) === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive"
                                    {{ old('status', $editing?->status) === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer flex-column flex-sm-row">
                        <a href="{{ route('customers.index') }}"
                            class="btn btn-light w-100 w-sm-auto mb-2 mb-sm-0">Cancel</a>
                        <button type="submit" class="btn btn-dark w-100 w-sm-auto m-0">
                            <i class="bi bi-save"></i>
                            {{ $editing ? 'Update Customer' : 'Save Customer' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- backdrop + body scroll lock when modal is open --}}
    @if ($openModal)
        <div class="modal-backdrop fade show"></div>
        <style>
            body {
                overflow: hidden;
                padding-right: 0 !important;
            }
        </style>
    @endif


    {{-- delete modal --}}
    @php $openDelete = (bool) $deleting; @endphp

    <div class="modal fade {{ $openDelete ? 'show' : '' }}" id="deleteModal" tabindex="-1"
        style="{{ $openDelete ? 'display:block;' : '' }}" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header border-0 pb-0">
                    <a href="{{ route('customers.index') }}" class="btn-close ms-auto"></a>
                </div>

                <div class="modal-body pt-2 text-center px-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3.5rem;"></i>
                    <h4 class="mt-3 mb-2">Delete Customer</h4>
                    <p class="mb-1">
                        Are you sure you want to delete
                        <strong>{{ $deleting?->first_name }} {{ $deleting?->last_name }}</strong>?
                    </p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>

                <div class="modal-footer flex-column flex-sm-row justify-content-center border-0 pb-4 gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-light w-100 w-sm-auto">Cancel</a>

                    @if ($deleting)
                        <form method="POST" action="{{ route('customers.destroy', $deleting->id) }}"
                            class="m-0 w-100 w-sm-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Yes, Delete
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @if ($openDelete)
        <div class="modal-backdrop fade show"></div>
        <style>
            body {
                overflow: hidden;
                padding-right: 0 !important;
            }
        </style>
    @endif


@endsection
