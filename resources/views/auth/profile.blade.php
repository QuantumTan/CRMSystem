@extends('layouts.app')

@section('title', 'Profile')

@section('content')
	<div class="row g-3">
		<div class="col-12 col-lg-8">
			<div class="card border-0 shadow-sm">
				<div class="card-body p-4">
					<h2 class="h5 mb-3">My Profile</h2>
					<div class="mb-3">
						<label class="form-label text-muted small mb-1">Name</label>
						<div class="fw-semibold">{{ auth()->user()->name }}</div>
					</div>

					<div class="mb-3">
						<label class="form-label text-muted small mb-1">Email</label>
						<div>{{ auth()->user()->email }}</div>
					</div>

					<div class="mb-0">
						<label class="form-label text-muted small mb-1">Role</label>
						<div class="text-uppercase">{{ auth()->user()->role }}</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body p-4">
					<h2 class="h6 mb-3">Session</h2>
					<p class="text-muted small mb-3">Use this action to securely sign out of your account.</p>

					<form action="{{ route('logout') }}" method="POST" class="d-inline">
						@csrf
						<button type="submit" class="btn btn-sm btn-outline-danger">
							<i class="bi bi-box-arrow-right"></i>
							<span class="d-none d-md-inline ms-1">Logout</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
