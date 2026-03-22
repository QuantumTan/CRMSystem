@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
	<h1 class="h3 mb-1">Admin Dashboard</h1>
	<p class="text-muted mb-4">Role: Admin</p>

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