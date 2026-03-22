<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>@yield('title', 'Admin') - {{ config('app.name', 'NexLink CRM') }}</title>

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link rel="preconnect" href="https://fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
		integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

	@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
		@vite(['resources/sass/app.scss', 'resources/js/app.js'])
	@endif
</head>

<body class="bg-light">
	<nav class="navbar navbar-expand-lg bg-white border-bottom">
		<div class="container">
			<a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">{{ config('app.name', 'NexLink CRM') }}</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
				aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="adminNav">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
						<a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('users.index') }}">Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ route('profile') }}">Profile</a>
					</li>
				</ul>

				<form action="{{ route('logout') }}" method="POST" class="d-flex">
					@csrf
					<button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
				</form>
			</div>
		</div>
	</nav>

	<main class="container py-4">
		@if (session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif

		@yield('content')
	</main>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
	</script>
</body>

</html>
