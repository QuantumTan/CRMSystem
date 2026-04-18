<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM') - {{ config('app.name', 'NexLink CRM') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @endif
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-light crm-body">
    @php
        $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));
        $segments = request()->segments();
        $breadcrumbs = [
            [
                'label' => 'Dashboard',
                'url' => route('dashboard'),
            ],
        ];

        if (! empty($segments)) {
            $path = '';
            $lastIndex = array_key_last($segments);

            foreach ($segments as $index => $segment) {
                if ($segment === 'dashboard') {
                    continue;
                }

                $path .= '/'.$segment;
                $isLast = $index === $lastIndex;

                $breadcrumbs[] = [
                    'label' => $isLast ? $pageTitle : \Illuminate\Support\Str::headline(str_replace('-', ' ', $segment)),
                    'url' => $isLast ? null : url($path),
                ];
            }
        }

        if (count($breadcrumbs) === 1 && $pageTitle !== 'Dashboard') {
            $breadcrumbs[] = [
                'label' => $pageTitle,
                'url' => null,
            ];
        }
    @endphp
    <div class="crm-shell">
        @include('layouts.partials.sidebar')

        <div class="crm-main">
            <header class="crm-topbar px-3 px-lg-4 py-3">
                <div class="crm-topbar-inner d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" id="sidebarMobileToggle"
                            aria-label="Open sidebar">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="crm-topbar-heading min-w-0">
                            <div class="crm-eyebrow mb-1">Workspace · {{ ucfirst(auth()->user()->role) }}</div>
                            <h1 class="crm-page-title mb-1">{{ $pageTitle }}</h1>
                            <div>
                                @hasSection('breadcrumbs')
                                    @yield('breadcrumbs')
                                @else
                                    <nav aria-label="breadcrumb" class="crm-breadcrumb-wrap">
                                        <ol class="breadcrumb crm-breadcrumb mb-0">
                                            @foreach ($breadcrumbs as $crumb)
                                                @if ($crumb['url'])
                                                    <li class="breadcrumb-item">
                                                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                                                    </li>
                                                @else
                                                    <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                                                @endif
                                            @endforeach
                                        </ol>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="crm-topbar-user d-none d-md-flex align-items-center gap-3">
                        <div class="crm-topbar-user-meta text-end">
                            <div class="crm-topbar-user-name">{{ auth()->user()->name }}</div>
                            <div class="crm-topbar-user-role">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                        <div class="crm-topbar-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="crm-content p-3 p-lg-4">
                @if (session('success'))
                    <div class="alert alert-success crm-alert" role="alert">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger crm-alert" role="alert">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="crm-backdrop" id="crmBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    @stack('scripts')
</body>

</html>
