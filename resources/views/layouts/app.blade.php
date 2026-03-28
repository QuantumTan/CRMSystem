<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM') - {{ config('app.name', 'NexLink CRM') }}</title>

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
    <div class="crm-shell">
        @include('layouts.partials.sidebar')

        <div class="crm-main">
            <header class="crm-topbar bg-white border-bottom px-3 px-lg-4 py-3">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" id="sidebarMobileToggle"
                            aria-label="Open sidebar">
                            <i class="bi bi-list"></i>
                        </button>
                        {{-- Page header --}}
                        
                        <div>
                            <h1 class="fs-3 fw-bold mb-0">@yield('title', 'Dashboard')</h1>
                            <div class="small text-muted">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-3 p-lg-4">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="crm-backdrop" id="crmBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
