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
                        <div class="h3 h2-md fw-semibold mb-1" id="totalCustomers">{{$customerThisMonth}}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">Active</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="activeCustomers">{{$customerIsActive}}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">Inactive</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="inactiveCustomers">{{$customerIsInactive}}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body px-3 py-3">
                        <div class="text-muted small text-uppercase mb-1 text-truncate">New This Month</div>
                        <div class="h3 h2-md fw-semibold mb-1" id="newThisMonth">{{$customerThisMonth}}</div>
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
                            <th class="small text-muted" width="50">#</th>
                            <th class="small text-muted">Customer</th>
                            <th class="small text-muted">Email</th>
                            <th class="small text-muted d-none d-sm-table-cell">Phone</th>
                            <th class="small text-muted d-none d-lg-table-cell">Address</th>
                            <th class="small text-muted" width="100">Status</th>
                            <th class="small text-muted d-none d-md-table-cell" width="110">Created</th>
                            <th class="small text-muted text-end text-sm-start" width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        {{-- TODO populate --}}
                        {{-- same  d-none d-sm-table-cell, etc classes on the <td> tags --}}
                    </tbody>
                </table>
            </div>

            {{-- pagination --}}
            <div
                class="card-footer bg-white border-top d-flex flex-column flex-sm-row align-items-center justify-content-between py-3 gap-3">
                <div class="small text-muted text-center text-sm-start">
                    Showing <span id="showingStart">0</span>–<span id="showingEnd">0</span>
                    of <span id="totalRecords">0</span> customers
                </div>
                <nav class="w-100 w-sm-auto d-flex justify-content-center justify-content-sm-end">
                    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center" id="pagination">
                        {{-- Pagination will be populated by JavaScript --}}
                    </ul>
                </nav>
            </div>

        </div>
    </div>

    {{-- add edit modal --}}
    <div class="modal fade" id="customerModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="customerForm" onsubmit="return saveCustomer(event)">
                    <div class="modal-body">
                        <input type="hidden" id="customerId" value="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control" placeholder="Juan dela Cruz"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" id="fieldEmail" class="form-control" placeholder="juan@example.com"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" id="fieldPhone" class="form-control" placeholder="+63 9XX XXX XXXX">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Address</label>
                            <textarea id="fieldAddress" class="form-control" rows="2" placeholder="City, Province"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select id="fieldStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer flex-column flex-sm-row">
                        <button type="button" class="btn btn-light w-100 w-sm-auto mb-2 mb-sm-0"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark w-100 w-sm-auto m-0">
                            <i class="bi bi-save"></i> Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- delete modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-center mb-3">Delete Customer</h4>
                    <p class="text-center mb-1">Are you sure you want to delete <strong id="deleteCustomerName"></strong>?
                    </p>
                    <p class="text-center text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer flex-column flex-sm-row justify-content-center border-0 pb-4">
                    <button type="button" class="btn btn-light w-100 w-sm-auto mb-2 mb-sm-0"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger w-100 w-sm-auto m-0"">
                        <i class="bi bi-trash"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
