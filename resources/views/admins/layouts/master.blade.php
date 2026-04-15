<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('head')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="me-2">
            <span class="fw-bold text-danger">Admin Panel</span>
        </div>

        <ul class="sidebar-menu">
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li class="menu-divider"></li>
            <li class="menu-header"><span>CUSTOMERS</span></li>
            <li><a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers') && !request('status') ? 'active' : '' }}"><i class="bi bi-people"></i><span>All Customers</span></a></li>
            <li><a href="{{ route('admin.customers', ['status' => 'active']) }}" class="{{ request('status') === 'active' ? 'active' : '' }}"><i class="bi bi-person-check"></i><span>Active</span></a></li>
            <li><a href="{{ route('admin.customers', ['status' => 'expired']) }}" class="{{ request('status') === 'expired' ? 'active' : '' }}"><i class="bi bi-person-x"></i><span>Expired</span></a></li>
            <li class="menu-divider"></li>
            <li class="menu-header"><span>SUBSCRIPTIONS</span></li>
            <li><a href="{{ route('admin.plans') }}" class="{{ request()->routeIs('admin.plans') ? 'active' : '' }}"><i class="bi bi-boxes"></i><span>Plans</span></a></li>
            <li><a href="{{ route('admin.subscriptions') }}" class="{{ request()->routeIs('admin.subscriptions') && request('status') !== 'expiring' ? 'active' : '' }}"><i class="bi bi-calendar-check"></i><span>Active Subscriptions</span></a></li>
            <li><a href="{{ route('admin.subscriptions', ['status' => 'expiring']) }}" class="{{ request()->routeIs('admin.subscriptions') && request('status') === 'expiring' ? 'active' : '' }}"><i class="bi bi-calendar-x"></i><span>Expiring Soon</span></a></li>
            <li class="menu-divider"></li>
            <li class="menu-header"><span>PAYMENTS</span></li>
            <li><a href="{{ route('admin.payments.queue') }}" class="{{ request()->routeIs('admin.payments.queue') ? 'active' : '' }}"><i class="bi bi-hourglass-split"></i><span>Pending Payments</span></a></li>
            <li><a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'active' : '' }}"><i class="bi bi-credit-card"></i><span>All Payments</span></a></li>
            <li><a href="{{ route('admin.invoices') }}" class="{{ request()->routeIs('admin.invoices') || request()->routeIs('admin.invoices.show') ? 'active' : '' }}"><i class="bi bi-receipt"></i><span>Invoices</span></a></li>
            <li class="menu-divider"></li>
            <li class="menu-header"><span>REPORTS</span></li>
            <li><a href="{{ route('admin.page', ['page' => 'reports']) }}" class="{{ request()->routeIs('admin.page') && request()->route('page') === 'reports' ? 'active' : '' }}"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
            <li class="menu-divider"></li>
            <li class="menu-header"><span>SETTINGS</span></li>
            <li><a href="{{ route('admin.admins') }}" class="{{ request()->routeIs('admin.admins') ? 'active' : '' }}"><i class="bi bi-shield-check"></i><span>Admin Users</span></a></li>
            <li><a href="{{ route('admin.page', ['page' => 'settings']) }}" class="{{ request()->routeIs('admin.page') && request()->route('page') === 'settings' ? 'active' : '' }}"><i class="bi bi-gear"></i><span>System Settings</span></a></li>
        </ul>

        <div class="sidebar-footer">
            <button class="btn btn-outline-danger w-100" onclick="adminLogout()">
                <i class="bi bi-box-arrow-left me-2"></i>
                <span>Logout</span>
            </button>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-link mobile-menu-toggle d-lg-none p-0 text-dark" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <div>
                        <h4 class="mb-0">@yield('page_title', 'Dashboard')</h4>
                        <small class="text-muted">@yield('page_subtitle', 'Welcome back, Admin!')</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $pendingPayments ?? 0 }}</span>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleLanguage()"><i class="bi bi-globe"></i></button>
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=dc3545&color=fff" alt="Admin" class="rounded-circle" width="35" height="35">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('admin.page', ['page' => 'profile']) }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.page', ['page' => 'settings']) }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="adminLogout()"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </main>

    <form id="adminLogoutForm" method="POST" action="{{ route('admin.logout') }}" class="d-none">
        @csrf
    </form>

    @yield('modals')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        function adminLogout() {
            if (confirm('Are you sure you want to logout?')) {
                sessionStorage.removeItem('adminToken');
                sessionStorage.removeItem('adminUsername');
                sessionStorage.removeItem('isAdmin');
                document.getElementById('adminLogoutForm').submit();
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
