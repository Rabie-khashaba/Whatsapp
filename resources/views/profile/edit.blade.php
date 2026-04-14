@extends('layouts.master')

@section('title', 'My Profile - WhatsApp Campaign Platform')
@section('page-title', 'My Profile')
@section('page-title-ar', 'ملفي الشخصي')

@php
    $planName = $subscription?->plan?->name ?? $customer?->plan ?? 'No Plan';
    $statusValue = strtolower((string) ($subscription?->status ?? $customer?->status ?? 'inactive'));
    $statusBadge = match ($statusValue) {
        'active' => 'success',
        'pending' => 'warning text-dark',
        'expired' => 'danger',
        default => 'secondary',
    };
    $expiryDate = $subscription?->end_date ?? $customer?->expiry_date;
@endphp

@section('content')
@if (session('status') === 'profile-updated')
    <div class="alert alert-success">Profile updated successfully.</div>
@endif

@if (session('status') === 'password-updated')
    <div class="alert alert-success">Password changed successfully.</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="dashboard-card text-center">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=25D366&color=fff"
                alt="Profile" class="rounded-circle mb-3" width="150" height="150">
            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted mb-2">{{ $user->email }}</p>
            <p class="text-muted mb-3">{{ $user->phone }}</p>
            <div class="mb-3">
                <span class="badge bg-primary">{{ $planName }}</span>
            </div>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil me-2"></i>
                <span data-en="Edit Profile" data-ar="تعديل الملف">Edit Profile</span>
            </button>
        </div>

        <div class="dashboard-card mt-3">
            <h6 class="mb-3">Account Status</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Member Since</span>
                <strong>{{ $user->created_at?->format('Y-m-d') ?? '-' }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Subscription Status</span>
                <span class="badge bg-{{ str_contains($statusBadge, ' ') ? 'warning' : $statusBadge }}">{{ ucfirst($statusValue) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Expires On</span>
                <strong>{{ $expiryDate?->format('Y-m-d') ?? '-' }}</strong>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="dashboard-card">
            <h5 class="mb-3">Personal Information</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">Full Name</label>
                    <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Email Address</label>
                    <p class="mb-0 fw-semibold">{{ $user->email }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Phone Number</label>
                    <p class="mb-0 fw-semibold">{{ $user->phone }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Country Code</label>
                    <p class="mb-0 fw-semibold">{{ $user->country_code ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-3">
            <h5 class="mb-3">Subscription Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">Current Plan</label>
                    <p class="mb-0"><span class="badge bg-primary">{{ $planName }}</span></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Billing Cycle</label>
                    <p class="mb-0 fw-semibold">{{ ucfirst((string) ($subscription?->billing_cycle ?? $customer?->billing_cycle ?? '-')) }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Max Instances</label>
                    <p class="mb-0 fw-semibold">{{ $activeInstances }} / {{ $customer?->max_instances ?? 0 }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Total Paid</label>
                    <p class="mb-0 fw-semibold text-success">${{ number_format($totalPaid, 2) }}</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-up-circle me-2"></i>
                    <span data-en="Upgrade Plan" data-ar="ترقية الباقة">Upgrade Plan</span>
                </a>
            </div>
        </div>

        <div class="dashboard-card mt-3">
            <h5 class="mb-3">Change Password</h5>
            <form method="POST" action="{{ route('password.update') }}">
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
                            <i class="bi bi-shield-check me-2"></i>
                            <span data-en="Change Password" data-ar="تغيير كلمة المرور">Change Password</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Code</label>
                        <input type="text" class="form-control" name="country_code" value="{{ old('country_code', $user->country_code) }}">
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

@section('scripts')
@if ($errors->has('name') || $errors->has('email') || $errors->has('phone') || $errors->has('country_code'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('editProfileModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
@endif
@endsection
