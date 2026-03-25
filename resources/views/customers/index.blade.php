@extends('layouts.app')

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
            @if ($isAdmin || $isSales)
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Customer
                </a>
            @endif
        </div>

        @include('customers.partials._stats')
        @include('customers.partials.table')

    </div>
@endsection
