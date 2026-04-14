<<<<<<< HEAD
@extends('admins.layouts.master')

@section('title', 'Admin Profile - Admin Panel')
@section('page_title', 'Admin Profile')
@section('page_subtitle', 'Manage your admin account')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="dashboard-card text-center">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&size=150&background=dc3545&color=fff"
                alt="Admin" class="rounded-circle mb-3" width="150" height="150">
            <h4 class="mb-1">{{ $admin->name }}</h4>
            <p class="text-muted mb-3">{{ $admin->email }}</p>
            <div class="mb-3">
                <span class="badge bg-danger text-uppercase">{{ $admin->display_role }}</span>
            </div>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editAdminProfileModal">
                <i class="bi bi-pencil me-2"></i>Edit Profile
            </button>
        </div>

        <div class="dashboard-card mt-3">
            <h6 class="mb-3">Account Info</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Login Phone</span>
                <strong>{{ $admin->phone ?: '-' }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Status</span>
                <span class="badge bg-success">Active</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Joined</span>
                <strong>{{ $admin->created_at?->diffForHumans() ?? '-' }}</strong>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="dashboard-card">
            <h5 class="mb-3">Personal Information</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">Full Name</label>
                    <p class="mb-0 fw-semibold">{{ $admin->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Email Address</label>
                    <p class="mb-0 fw-semibold">{{ $admin->email }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Phone</label>
                    <p class="mb-0 fw-semibold">{{ $admin->phone ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Role</label>
                    <p class="mb-0"><span class="badge bg-danger text-uppercase">{{ $admin->display_role }}</span></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Country Code</label>
                    <p class="mb-0 fw-semibold">{{ $admin->country_code ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Admin ID</label>
                    <p class="mb-0 fw-semibold">#{{ $admin->id }}</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-3">
            <h5 class="mb-3">Activity Statistics</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-center p-3 border rounded">
                        <h3 class="text-primary mb-0">{{ $profileStats['customers_managed'] }}</h3>
                        <small class="text-muted">Customers Managed</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 border rounded">
                        <h3 class="text-success mb-0">{{ $profileStats['payments_approved'] }}</h3>
                        <small class="text-muted">Payments Approved</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 border rounded">
                        <h3 class="text-warning mb-0">{{ $profileStats['reports_generated'] }}</h3>
                        <small class="text-muted">Invoices Generated</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 border rounded">
                        <h3 class="text-info mb-0">{{ $profileStats['total_actions'] }}</h3>
                        <small class="text-muted">Total Actions</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-3">
            <h5 class="mb-3">Change Password</h5>
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Change Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="editAdminProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Admin Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $admin->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $admin->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $admin->phone) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Code</label>
                        <input type="text" class="form-control" name="country_code" value="{{ old('country_code', $admin->country_code) }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
=======
﻿<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <script>sessionStorage.setItem('adminToken','server-session');sessionStorage.setItem('isAdmin','true');</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Admin Panel</title>
    <base href="/" />
    <link rel="icon" type="image/png" href="images/favicon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="images/logo.png" alt="Logo" class="me-2">
            <span class="fw-bold text-danger">Admin Panel</span>
        </div>

        <ul class="sidebar-menu">
            <li><a href="admin-dashboard.html"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span>CUSTOMERS</span></li>
            <li><a href="admin-customers.html"><i class="bi bi-people"></i><span>All Customers</span></a></li>
            <li><a href="admin-customers.html?status=active"><i class="bi bi-person-check"></i><span>Active</span></a></li>
            <li><a href="admin-customers.html?status=expired"><i class="bi bi-person-x"></i><span>Expired</span></a></li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span>SUBSCRIPTIONS</span></li>
            <li><a href="admin-plans.html"><i class="bi bi-boxes"></i><span>Plans</span></a></li>
            <li><a href="admin-subscriptions.html"><i class="bi bi-calendar-check"></i><span>Active Subscriptions</span></a></li>
            <li><a href="admin-subscriptions.html?status=expiring"><i class="bi bi-calendar-x"></i><span>Expiring Soon</span></a></li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span>PAYMENTS</span></li>
            <li><a href="admin-payments-queue.html"><i class="bi bi-hourglass-split"></i><span>Pending Payments</span></a></li>
            <li><a href="admin-payments.html"><i class="bi bi-credit-card"></i><span>All Payments</span></a></li>
            <li><a href="admin-invoices.html"><i class="bi bi-receipt"></i><span>Invoices</span></a></li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span>REPORTS</span></li>
            <li><a href="admin-reports.html"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
            
            <li class="menu-divider"></li>
            <li class="menu-header"><span>SETTINGS</span></li>
            <li><a href="admin-admins.html"><i class="bi bi-shield-check"></i><span>Admin Users</span></a></li>
            <li><a href="admin-settings.html"><i class="bi bi-gear"></i><span>System Settings</span></a></li>
        </ul>

        <div class="sidebar-footer">
            <button class="btn btn-outline-danger w-100" onclick="adminLogout()">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-link mobile-menu-toggle d-lg-none p-0 text-dark" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <div>
                        <h4 class="mb-0">Admin Profile</h4>
                        <small class="text-muted">Manage your admin account</small>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleLanguage()">
                    <i class="bi bi-globe"></i>
                </button>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="dashboard-card text-center">
                    <img src="" alt="Admin" class="rounded-circle mb-3" width="150" height="150" id="adminAvatar">
                    <h4 class="mb-1" id="adminName">Loading...</h4>
                    <p class="text-muted mb-3" id="adminEmail">admin@example.com</p>
                    <div class="mb-3">
                        <span class="badge bg-danger" id="adminRole">SUPER ADMIN</span>
                    </div>
                    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editAdminModal">
                        <i class="bi bi-pencil me-2"></i>Edit Profile
                    </button>
                </div>

                <div class="dashboard-card mt-3">
                    <h6 class="mb-3">Account Info</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Username</span>
                        <strong id="adminUsername">admin</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status</span>
                        <span class="badge bg-success" id="adminStatus">Active</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Login</span>
                        <strong id="lastLogin">2 hours ago</strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="dashboard-card">
                    <h5 class="mb-3">Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Full Name</label>
                            <p class="mb-0 fw-semibold" id="fullName">John Doe</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email Address</label>
                            <p class="mb-0 fw-semibold" id="emailAddress">john@admin.com</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Username</label>
                            <p class="mb-0 fw-semibold" id="usernameDisplay">johndoe</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Role</label>
                            <p class="mb-0"><span class="badge bg-danger" id="roleDisplay">Super Admin</span></p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mt-3">
                    <h5 class="mb-3">Activity Statistics</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h3 class="text-primary mb-0">156</h3>
                                <small class="text-muted">Customers Managed</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h3 class="text-success mb-0">89</h3>
                                <small class="text-muted">Payments Approved</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h3 class="text-warning mb-0">23</h3>
                                <small class="text-muted">Reports Generated</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h3 class="text-info mb-0">340</h3>
                                <small class="text-muted">Total Actions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mt-3">
                    <h5 class="mb-3">Change Password</h5>
                    <form id="changePasswordForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" onclick="changeAdminPassword()">
                                    <i class="bi bi-shield-check me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editAdminForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAdminProfile()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/admin-profile.js"></script>
</body>
</html>


>>>>>>> f9389bb0657d89ba01c4cde0b6d312a02bd1a402
