<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WhatsApp Campaign Platform')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('partials.sidebar')

    <main class="main-content">
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-link d-lg-none" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h4 class="mb-0 d-inline-block" data-en="@yield('page-title', 'Dashboard')" data-ar="@yield('page-title-ar', 'لوحة التحكم')">@yield('page-title', 'Dashboard')</h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="search-box d-none d-md-block">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search here..." data-en-placeholder="Search here..." data-ar-placeholder="ابحث هنا...">
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleLanguage()">
                        <i class="bi bi-globe"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=25D366&color=fff" alt="User" class="rounded-circle" width="35" height="35">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i><span data-en="Profile" data-ar="الملف الشخصي">Profile</span></a></li>
                            <li><a class="dropdown-item" href="{{ route('settings.edit') }}"><i class="bi bi-gear me-2"></i><span data-en="Settings" data-ar="الإعدادات">Settings</span></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#"
                                    onclick="event.preventDefault(); document.getElementById('adminLogoutForm').submit();">
                                    <i class="bi bi-box-arrow-left me-2"></i>
                                    <span data-en="Logout" data-ar="تسجيل الخروج">Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </main>

    @yield('modals')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    @yield('scripts')
</body>
</html>
