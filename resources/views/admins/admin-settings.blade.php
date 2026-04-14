@extends('admins.layouts.master')

@section('title', 'System Settings - Admin Panel')
@section('page_title', 'System Settings')
@section('page_subtitle', 'Configure system preferences')

@php($activeTab = request('tab', 'general'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button>
    </li>
    <li class="nav-item">
        <button class="nav-link {{ $activeTab === 'payment' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#payment" type="button">Payment Methods</button>
    </li>
    <li class="nav-item">
        <button class="nav-link {{ $activeTab === 'email' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#email" type="button">Email Settings</button>
    </li>
    <li class="nav-item">
        <button class="nav-link {{ $activeTab === 'notifications' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#notifications" type="button">Notifications</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="general">
        <div class="dashboard-card">
            <h5 class="mb-3">General Settings</h5>
            <form method="POST" action="{{ route('admin.settings.update', 'general') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" class="form-control" name="site_name" value="{{ old('site_name', $generalSettings->site_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site URL</label>
                        <input type="url" class="form-control" name="site_url" value="{{ old('site_url', $generalSettings->site_url) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Support Email</label>
                        <input type="email" class="form-control" name="support_email" value="{{ old('support_email', $generalSettings->support_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Support Phone</label>
                        <input type="text" class="form-control" name="support_phone" value="{{ old('support_phone', $generalSettings->support_phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Site Description</label>
                        <textarea class="form-control" name="site_description" rows="3">{{ old('site_description', $generalSettings->site_description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save General Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeTab === 'payment' ? 'show active' : '' }}" id="payment">
        <div class="dashboard-card">
            <h5 class="mb-3">Payment Methods</h5>
            <form method="POST" action="{{ route('admin.settings.update', 'payment') }}">
                @csrf
                <div class="mb-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="vodafoneCash" name="vodafone_cash_enabled" value="1" {{ old('vodafone_cash_enabled', $paymentSettings->vodafone_cash_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="vodafoneCash">
                            <strong>Vodafone Cash</strong>
                            <small class="d-block text-muted">Allow payments via Vodafone Cash</small>
                        </label>
                    </div>
                    <div class="ms-4">
                        <label class="form-label">Vodafone Cash Number</label>
                        <input type="text" class="form-control mb-2" name="vodafone_cash_number" value="{{ old('vodafone_cash_number', $paymentSettings->vodafone_cash_number) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="bankTransfer" name="bank_transfer_enabled" value="1" {{ old('bank_transfer_enabled', $paymentSettings->bank_transfer_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="bankTransfer">
                            <strong>Bank Transfer</strong>
                            <small class="d-block text-muted">Allow payments via bank transfer</small>
                        </label>
                    </div>
                    <div class="ms-4">
                        <label class="form-label">Bank Name</label>
                        <input type="text" class="form-control mb-2" name="bank_name" value="{{ old('bank_name', $paymentSettings->bank_name) }}">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control mb-2" name="bank_account_number" value="{{ old('bank_account_number', $paymentSettings->bank_account_number) }}">
                        <label class="form-label">IBAN</label>
                        <input type="text" class="form-control" name="bank_iban" value="{{ old('bank_iban', $paymentSettings->bank_iban) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="creditCard" name="credit_card_enabled" value="1" {{ old('credit_card_enabled', $paymentSettings->credit_card_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="creditCard">
                            <strong>Credit Card</strong>
                            <small class="d-block text-muted">Allow payments via credit card</small>
                        </label>
                    </div>
                </div>

                <button class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Payment Settings
                </button>
            </form>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeTab === 'email' ? 'show active' : '' }}" id="email">
        <div class="dashboard-card">
            <h5 class="mb-3">Email Settings</h5>
            <form method="POST" action="{{ route('admin.settings.update', 'email') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" name="smtp_host" value="{{ old('smtp_host', $emailSettings->smtp_host) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" class="form-control" name="smtp_port" value="{{ old('smtp_port', $emailSettings->smtp_port) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" name="smtp_username" value="{{ old('smtp_username', $emailSettings->smtp_username) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" name="smtp_password" value="{{ old('smtp_password', $emailSettings->smtp_password) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" class="form-control" name="from_email" value="{{ old('from_email', $emailSettings->from_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" class="form-control" name="from_name" value="{{ old('from_name', $emailSettings->from_name) }}">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Email Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeTab === 'notifications' ? 'show active' : '' }}" id="notifications">
        <div class="dashboard-card">
            <h5 class="mb-3">Notification Settings</h5>
            <form method="POST" action="{{ route('admin.settings.update', 'notifications') }}">
                @csrf
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifyNewCustomer" name="notify_new_customer" value="1" {{ old('notify_new_customer', $notificationSettings->notify_new_customer) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifyNewCustomer">
                            <strong>New Customer Registration</strong>
                            <small class="d-block text-muted">Notify admins when a new customer registers</small>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifyNewPayment" name="notify_new_payment" value="1" {{ old('notify_new_payment', $notificationSettings->notify_new_payment) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifyNewPayment">
                            <strong>New Payment Request</strong>
                            <small class="d-block text-muted">Notify admins when payment is submitted</small>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifyExpiring" name="notify_expiring" value="1" {{ old('notify_expiring', $notificationSettings->notify_expiring) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifyExpiring">
                            <strong>Expiring Subscriptions</strong>
                            <small class="d-block text-muted">Notify customers 3 days before expiry</small>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifyExpired" name="notify_expired" value="1" {{ old('notify_expired', $notificationSettings->notify_expired) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notifyExpired">
                            <strong>Expired Subscriptions</strong>
                            <small class="d-block text-muted">Notify customers when subscription expires</small>
                        </label>
                    </div>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Notification Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
