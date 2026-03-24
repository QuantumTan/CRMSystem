@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h1 class="h3 mb-1">Dashboard</h1>
			<p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}.</p>
		</div>
		<form action="{{ route('logout') }}" method="POST">
			@csrf
			<button type="submit" class="btn btn-outline-danger">Logout</button>
		</form>
	</div>

	<div class="row g-3">
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body">
					<p class="text-muted mb-1">Total Customers</p>
					<h2 class="h4 mb-0">{{ $data['totalCustomers'] ?? 0 }}</h2>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body">
					<p class="text-muted mb-1">Active Leads</p>
					<h2 class="h4 mb-0">{{ $data['totalActiveLeads'] ?? 0 }}</h2>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body">
					<p class="text-muted mb-1">Completed Follow-ups</p>
					<h2 class="h4 mb-0">{{ $data['completedFollowUps'] ?? 0 }}</h2>
				</div>
			</div>
		</div>
	</div>
@endsection
