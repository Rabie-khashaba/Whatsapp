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
