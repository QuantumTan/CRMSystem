@php
    $user = auth()->user();
    $role = $user?->role;

    $isAdmin = $role === 'admin';
    $isManager = $role === 'manager';
    $isSales = $role === 'sales';

    $leadCount = 0;
    $activityCount = 0;
    $followUpCount = 0;

    try {
        $leadCount = \App\Models\Lead::count();
        $activityCount = \App\Models\Activity::count();
        $followUpCount = \App\Models\FollowUp::count();
    } catch (\Throwable $e) {
        // Keep sidebar usable even when tables are not ready.
    }

    $nameParts = preg_split('/\s+/', trim((string) ($user?->name ?? 'User')));
    $initials = '';

    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    if ($initials === '') {
        $initials = 'U';
    }
@endphp

<aside class="crm-sidebar" id="crmSidebar">
    <div class="crm-brand px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-2">
        <a href="{{ route('dashboard') }}"
            class="crm-brand-link text-decoration-none d-flex align-items-center gap-2 text-white">
            <img src="{{ asset('assets/images/crm_logo.png') }}" alt="NexLink"
                style="width:36px;height:36px;object-fit:contain;">
            <span class="crm-label fw-semibold">NexLink CRM</span>
        </a>

        <button class="crm-sidebar-toggle btn btn-sm d-none d-lg-inline-flex" type="button" id="sidebarToggle"
            aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <div class="crm-sidebar-scroll px-2 py-2">
        <nav class="nav flex-column" id="crmSidebarNav">
            <section class="crm-nav-section" data-nav-section>
                <div class="crm-section-title crm-label">Main</div>
                <div class="d-flex flex-column gap-0">
                    <a href="{{ route('dashboard') }}" data-nav-link data-nav-label="Dashboard" title="Dashboard"
                        class="crm-nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="crm-label">Dashboard</span>
                    </a>
                </div>
            </section>

            @if ($isAdmin || $isManager)
                <section class="crm-nav-section" data-nav-section>
                    <div class="crm-section-title crm-label">Performance</div>
                    <div class="d-flex flex-column gap-0">
                        <a href="{{ route('reports.index') }}" data-nav-link data-nav-label="Reports" title="Reports"
                            class="crm-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span class="crm-label">Reports</span>
                        </a>
                    </div>
                </section>
            @endif

            <section class="crm-nav-section" data-nav-section>
                <div class="crm-section-title crm-label">Pipeline</div>
                <div class="d-flex flex-column gap-0">
                    @if ($isAdmin || $isManager || $isSales)
                        <a href="{{ route('customers.index') }}" data-nav-link data-nav-label="Customers"
                            title="Customers"
                            class="crm-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span class="crm-label">Customers</span>
                        </a>
                    @endif

                    @if ($isAdmin || $isManager || $isSales)
                        <a href="{{ route('leads.index') }}" data-nav-link data-nav-label="Leads" title="Leads"
                            class="crm-nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                            <i class="bi bi-funnel"></i>
                            <span class="crm-label">Leads</span>
                            @if ($leadCount > 0)
                                <span class="crm-badge crm-badge-red ms-auto">{{ $leadCount }}</span>
                            @endif
                        </a>
                    @endif

                    @if ($isAdmin || $isSales)
                        <a href="{{ route('activities.index') }}" data-nav-link data-nav-label="Activities"
                            title="Activities"
                            class="crm-nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-check"></i>
                            <span class="crm-label">Activities</span>
                            @if ($activityCount > 0)
                                <span class="crm-badge crm-badge-amber ms-auto">{{ $activityCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('follow-ups.index') }}" data-nav-link data-nav-label="Follow-ups"
                            title="Follow-ups"
                            class="crm-nav-link {{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                            <i class="bi bi-alarm"></i>
                            <span class="crm-label">Tasks & Reminders</span>
                            @if ($followUpCount > 0)
                                <span class="crm-badge crm-badge-red ms-auto">{{ $followUpCount }}</span>
                            @endif
                        </a>
                    @endif
                </div>
            </section>

            @if ($isAdmin)
                <section class="crm-nav-section" data-nav-section>
                    <div class="crm-section-title crm-label">Admin</div>
                    <div class="d-flex flex-column gap-0">
                        <a href="{{ route('users.index') }}" data-nav-link data-nav-label="Users" title="Users"
                            class="crm-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span class="crm-label">Users</span>
                        </a>

                        <a href="{{ route('settings.index') }}" data-nav-link data-nav-label="Settings"
                            title="Settings"
                            class="crm-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span class="crm-label">Settings</span>
                        </a>
                    </div>
                </section>
            @endif
        </nav>
    </div>

    <div class="crm-profile-footer px-2 pb-2 mt-auto border-top pt-2">
        <a href="{{ route('profile') }}"
            class="crm-profile-card text-decoration-none {{ request()->routeIs('profile') ? 'active' : '' }}">
            <span class="crm-avatar">{{ $initials }}</span>
            <span class="crm-profile-meta crm-label">
                <strong class="crm-profile-name">{{ $user?->name }}</strong>
                <small class="crm-profile-role">{{ ucfirst($role ?? 'User') }}</small>
            </span>
            <i class="bi bi-chevron-right crm-profile-arrow crm-label"></i>
        </a>
    </div>
</aside>
