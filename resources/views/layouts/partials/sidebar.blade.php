@php
    $user = auth()->user();
    $role = $user?->role;

    $isAdmin = $role === 'admin';
    $isManager = $role === 'manager';
    $isSales = $role === 'sales';
@endphp

<aside class="crm-sidebar" id="crmSidebar">
    <div class="crm-brand px-3 py-3 border-bottom d-flex align-items-center justify-content-between gap-2">
        <a href="{{ route('dashboard') }}" class="crm-brand-link text-decoration-none d-flex align-items-center gap-2 text-white">
            <span class="crm-brand-icon"><i class="bi bi-hexagon-fill"></i></span>
            <span class="crm-label fw-semibold">NexLink CRM</span>
        </a>

        <button class="crm-sidebar-toggle btn btn-sm d-none d-lg-inline-flex" type="button" id="sidebarToggle"
            aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <div class="crm-sidebar-scroll px-2 py-3">
        <nav class="nav flex-column gap-1">
            <a href="{{ route('dashboard') }}" class="crm-nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span class="crm-label">Dashboard</span>
            </a>

            @if ($isAdmin || $isSales)
                <a href="{{ route('customers.index') }}" class="crm-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span class="crm-label">Customers</span>
                </a>
            @endif

            @if ($isAdmin || $isManager || $isSales)
                <a href="{{ route('leads.index') }}" class="crm-nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="bi bi-funnel"></i>
                    <span class="crm-label">Leads</span>
                </a>

                <a href="{{ route('activities.index') }}" class="crm-nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-check"></i>
                    <span class="crm-label">Activities</span>
                </a>

                <a href="{{ route('follow-ups.index') }}" class="crm-nav-link {{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                    <i class="bi bi-alarm"></i>
                    <span class="crm-label">Follow-ups</span>
                </a>
            @endif

            @if ($isAdmin || $isManager)
                <a href="{{ route('reports.index') }}" class="crm-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span class="crm-label">Reports</span>
                </a>
            @endif

            @if ($isAdmin)
                <a href="{{ route('users.index') }}" class="crm-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>
                    <span class="crm-label">Users</span>
                </a>
            @endif

            @if($isAdmin || $isManager || $isSales)
                    <a href="{{ route('settings.index') }}" class="crm-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span class="crm-label">Settings</span>
        </a>
            @endif
        </nav>
    </div>

    <div class="px-2 pb-3 mt-auto border-top pt-3">


        <a href="{{ route('profile') }}" class="crm-nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span class="crm-label">Profile</span>
        </a>
    </div>
</aside>