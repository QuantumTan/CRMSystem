<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ config('app.name', 'NexLink CRM') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-white">
    <div class="container-fluid p-0 min-vh-100">
        <div class="row g-0 min-vh-100">

            {{-- Left panel --}}
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 nx-panel text-white position-relative overflow-hidden">
                <div style="position:relative;z-index:1;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ asset('assets/images/crm_logo.png') }}" alt="NexLink Logo" class="rounded-3"
                            style="width:52px;height:52px;object-fit:contain;background:rgba(255,255,255,.07);padding:4px;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0 fw-bold">NexLink</h5>
                            </div>
                            <small class="nx-muted-copy">Integrated CRM Solutions</small>
                        </div>
                    </div>

                    <hr style="border-color:rgba(255,255,255,.1);margin:1.75rem 0;">

                    <h1 class="fw-bold lh-sm mb-3 mt-"
                        style="max-width:460px;letter-spacing:-.03em;font-size:clamp(1.5rem,2.2vw,2rem);">
                        Manage leads, customers, and follow-ups in one place.
                    </h1>
                    <p class="mb-5">
                        Track every interaction, assign tasks to your team, and convert opportunities faster with
                        NexLink's unified dashboard.
                    </p>

                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="nx-icon-box"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="fw-semibold nx-feature-title">Centralized Customer Data</div>
                                <small class="nx-feature-copy">Keep all customer details and history in one unified view.</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="nx-icon-box"><i class="bi bi-kanban-fill"></i></div>
                            <div>
                                <div class="fw-semibold nx-feature-title">Lead Pipeline Visibility</div>
                                <small class="nx-feature-copy">Track lead status from first inquiry all the way to conversion.</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="nx-icon-box"><i class="bi bi-bell-fill"></i></div>
                            <div>
                                <div class="fw-semibold nx-feature-title">Smart Follow-up Reminders</div>
                                <small class="nx-feature-copy">Never miss an important customer action or deadline.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right panel --}}
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5 bg-white">
                <div class="card border shadow-sm rounded-4 w-100" style="max-width:460px;">
                    <div class="card-body p-4 p-lg-5">

                        <div class="d-flex d-lg-none align-items-center gap-2 mb-4">
                            <img src="{{ asset('assets/images/crm_logo.png') }}" alt="NexLink"
                                style="width:36px;height:36px;object-fit:contain;">
                            <span class="fw-bold fs-6" style="color:var(--nx-navy);">NexLink</span>
                        </div>

                        {{-- Error alert --}}
                        @if ($errors->any())
                            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-circle-fill shrink-0"></i>
                                <span class="small">{{ $errors->first() }}</span>
                            </div>
                        @endif

                        {{-- Heading --}}
                        <h2 class="fw-bold mb-1" style="letter-spacing:-.03em;color:var(--nx-navy);">Welcome back</h2>
                        <p class="text-secondary mb-4 small">Sign in to continue.</p>

                        <form method="POST" action="{{ route('login.attempt') }}">
                            @csrf

                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">Email address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-secondary rounded-start-3">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input id="email" type="email" name="email"
                                        class="form-control p-2 bg-light border-start-0  rounded-end-3 @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="you@company.com"
                                        required autofocus autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-semibold small mb-0">Password</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color:var(--nx-blue);">
                                            Forgot password?
                                        </a>
                                    @endif
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-secondary rounded-start-3">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input id="password" type="password" name="password"
                                        class="form-control bg-light border-start-0 border-end-0 p-2 @error('password') is-invalid @enderror"
                                        placeholder="Enter your password" required autocomplete="current-password">

                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Remember me --}}
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label small text-secondary" for="remember">
                                    Remember Me
                                </label>
                            </div>

                            {{-- Submit --}}
                            <button type="submit" class="btn btn-nexlink w-100 py-2 fw-semibold rounded-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign in to NexLink
                            </button>

                        </form>

                        <hr class="my-4 opacity-25">

                        <p class="text-center text-secondary mb-0" style="font-size:.75rem;">
                            By signing in you agree to our
                            <a href="#" class="text-secondary">Terms</a> &amp;
                            <a href="#" class="text-secondary">Privacy Policy</a>.
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>