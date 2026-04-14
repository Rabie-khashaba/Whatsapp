@extends('layouts.master')

@section('title', 'Settings - WhatsApp Campaign Platform')
@section('page-title', 'Settings')
@section('page-title-ar', 'الإعدادات')

@section('content')
@if (session('status') === 'settings-updated')
    <div class="alert alert-success">Settings updated successfully.</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Preferences</h5>
                    <p class="text-muted mb-0 small">Manage your account and notification preferences.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Language</label>
                        <select class="form-select" name="language" required>
                            <option value="en" {{ ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ ($settings['language'] ?? '') === 'ar' ? 'selected' : '' }}>Arabic</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <input type="text" class="form-control" name="timezone" value="{{ old('timezone', $settings['timezone'] ?? config('app.timezone')) }}" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" value="1" {{ ($settings['email_notifications'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="whatsappNotifications" name="whatsapp_notifications" value="1" {{ ($settings['whatsapp_notifications'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsappNotifications">WhatsApp Notifications</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="marketingNotifications" name="marketing_notifications" value="1" {{ ($settings['marketing_notifications'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="marketingNotifications">Marketing Updates</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-card mt-3">
            <h5 class="mb-3">Security</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                    <i class="bi bi-person me-2"></i>Edit Profile
                </a>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-shield-lock me-2"></i>Change Password
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dashboard-card">
            <h5 class="mb-3">Account Summary</h5>
            <div class="d-flex justify-content-between mb-2">
                <span>Name</span>
                <strong>{{ $user->name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Email</span>
                <strong>{{ $user->email }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Phone</span>
                <strong>{{ $user->phone }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>Plan</span>
                <strong>{{ $customer?->plan ?? 'No Plan' }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection
