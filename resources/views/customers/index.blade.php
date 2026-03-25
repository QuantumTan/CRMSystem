@extends('layouts.admin')

@section('title', 'Customers')

@php
    $isAdmin = auth()->user()?->role === 'admin';
    $isManager = auth()->user()?->role === 'manager';
    $isSales = auth()->user()?->role === 'sales';

    $roleLabel = match (true) {
        $isAdmin => 'Admin',
        $isManager => 'Manager',
        $isSales => 'Sales Staff',
        default => 'Staff',
    };
@endphp

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">

        {{-- Page Header --}}
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-semibold">Customers</h4>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                        {{ $roleLabel }}
                    </span>
                </div>
                <p class="text-muted mb-0 small">Viewing all customer records</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                <i class="bi bi-plus-lg"></i> Add Customer
            </button>
        </div>

        @include('customers.partials._stats')
        @include('customers.partials.table')

    </div>

    @include('customers.partials._modal-form')
    @include('customers.partials._modal-delete')
@endsection

@push('scripts')
    <script src="{{ asset('js/customers.js') }}"></script>
@endpush
