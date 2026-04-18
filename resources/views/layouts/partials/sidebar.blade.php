@php
    $user = auth()->user();
    $role = $user?->role;

    $isAdmin   = $role === 'admin';
    $isManager = $role === 'manager';
    $isSales   = $role === 'sales';

    $leadCount     = 0;
    $activityCount = 0;
    $followUpCount = 0;

    try {
        $leadCountQuery = \App\Models\Lead::query();
        $activityCountQuery = \App\Models\Activity::query();
        $followUpCountQuery = \App\Models\FollowUp::query();

        if ($isSales && $user) {
            $leadCountQuery->where('assigned_user_id', $user->id);
            $activityCountQuery->where('user_id', $user->id);
            $followUpCountQuery->where('user_id', $user->id);
        }

        $leadCount     = $leadCountQuery->count();
        $activityCount = $activityCountQuery->count();
        $followUpCount = $followUpCountQuery->count();
    } catch (\Throwable $e) {}

    $nameParts = preg_split('/\s+/', trim((string) ($user?->name ?? 'User')));
    $initials  = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if ($initials === '') $initials = 'U';
@endphp

<aside class="crm-sidebar" id="crmSidebar">

    {{-- Brand --}}
    <div class="crm-brand px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-2">
        <a href="{{ route('dashboard') }}"
            class="crm-brand-link text-decoration-none d-flex align-items-center gap-2 text-white">
            <img src="{{ asset('assets/images/crm_logo.png') }}" alt="NexLink"
                style="width:36px;height:36px;object-fit:contain;">
            <span class="crm-label fw-semibold">{{ config('app.name', 'NexLink CRM') }}</span>
        </a>
        <button class="crm-sidebar-toggle btn btn-sm d-none d-lg-inline-flex"
            type="button" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <div class="crm-sidebar-scroll px-2 py-2">
        <nav class="nav flex-column" id="crmSidebarNav">

            {{-- Main --}}
            <section class="crm-nav-section" data-nav-section>
                <div class="crm-section-title crm-label">Main</div>
                <div class="d-flex flex-column gap-0">
                    <a href="{{ route('dashboard') }}" data-nav-link data-nav-label="Dashboard"
                        title="Dashboard"
                        class="crm-nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                        <i class="bi bi-grid"></i>
                        <span class="crm-label">Dashboard</span>
                    </a>
                </div>
            </section>

            {{-- Performance --}}
            @if ($isAdmin || $isManager)
                <section class="crm-nav-section" data-nav-section>
                    <div class="crm-section-title crm-label">Performance</div>
                    <div class="d-flex flex-column gap-0">
                        <a href="{{ route('reports.index') }}" data-nav-link data-nav-label="Reports"
                            title="Reports"
                            class="crm-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-steps"></i>
                            <span class="crm-label">Reports</span>
                        </a>
                    </div>
                </section>
            @endif

            {{-- Pipeline --}}
            <section class="crm-nav-section" data-nav-section>
                <div class="crm-section-title crm-label">Pipeline</div>
                <div class="d-flex flex-column gap-0">

                    @if ($isAdmin || $isManager || $isSales)
                        <a href="{{ route('customers.index') }}" data-nav-link data-nav-label="Customers"
                            title="Customers"
                            class="crm-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="crm-label">Customers</span>
                        </a>
                    @endif

                    @if ($isAdmin || $isManager || $isSales)
                        <a href="{{ route('leads.index') }}" data-nav-link data-nav-label="Leads"
                            title="Leads"
                            class="crm-nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                            <i class="bi bi-send"></i>
                            <span class="crm-label">Leads</span>
                            @if ($leadCount > 0)
                                <span class="crm-badge crm-badge-red ms-auto">{{ $leadCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if ($isAdmin || $isManager || $isSales)
                        <a href="{{ route('activities.index') }}" data-nav-link data-nav-label="Activities"
                            title="Activities"
                            class="crm-nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                            <i class="bi bi-clipboard-pulse"></i>
                            <span class="crm-label">Activities</span>
                            @if ($activityCount > 0)
                                <span class="crm-badge crm-badge-amber ms-auto">{{ $activityCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('follow-ups.index') }}" data-nav-link data-nav-label="Tasks & Reminders"
                            title="Tasks & Reminders"
                            class="crm-nav-link {{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-all"></i>
                            <span class="crm-label">Follow Ups</span>
                            @if ($followUpCount > 0)
                                <span class="crm-badge crm-badge-red ms-auto">{{ $followUpCount }}</span>
                            @endif
                        </a>
                    @endif

                </div>
            </section>

            {{-- Admin --}}
            @if ($isAdmin)
                <section class="crm-nav-section" data-nav-section>
                    <div class="crm-section-title crm-label">Admin</div>
                    <div class="d-flex flex-column gap-0">
                        <a href="{{ route('users.index') }}" data-nav-link data-nav-label="Users"
                            title="Users"
                            class="crm-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span class="crm-label">Users</span>
                        </a>
                        <a href="{{ route('settings.index') }}" data-nav-link data-nav-label="Settings"
                            title="System Configuration"
                            class="crm-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-sliders2"></i>
                            <span class="crm-label">System Config</span>
                        </a>
                    </div>
                </section>
            @endif

        </nav>
    </div>

    {{-- Profile Footer --}}
    <div class="crm-profile-footer px-2 pb-2 mt-auto border-top pt-2">
        <div class="dropup crm-profile-menu-wrap">
            <button
                class="crm-profile-trigger w-100 {{ request()->routeIs('profile') ? 'active' : '' }}"
                type="button"
                id="crmProfileMenu"
                data-bs-toggle="dropdown"
                data-bs-offset="0,10"
                aria-expanded="false"
            >
                <span class="crm-avatar">{{ $initials }}</span>
                <span class="crm-profile-meta crm-label">
                    <strong class="crm-profile-name">{{ $user?->name }}</strong>
                    <small class="crm-profile-role">{{ ucfirst($role ?? 'User') }}</small>
                </span>
                <i class="bi bi-chevron-up crm-profile-arrow crm-label"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end crm-profile-dropdown" aria-labelledby="crmProfileMenu">
                <li>
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <i class="bi bi-person-circle me-2"></i>View Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

</aside>
