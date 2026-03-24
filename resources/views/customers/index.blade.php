@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    @php
        $isAdmin = auth()->user()?->role === 'admin';
        $isManager = auth()->user()?->role === 'manager';
        $selectedStatus = request('assignment_status');
    @endphp


@endsection
