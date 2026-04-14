@extends('admins.layouts.master')

@section('title', 'Customer Details - Admin Panel')
@section('page_title', 'Customer Details')
@section('page_subtitle', $customer->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="dashboard-card text-center">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}&background=dc3545&color=fff"
                alt="Customer" class="rounded-circle mb-3" width="100" height="100">
            <h4 class="mb-1">{{ $customer->name }}</h4>
            <p class="text-muted mb-2">{{ $customer->email ?: '-' }}</p>
            <p class="text-muted mb-3">{{ $customer->phone ?: '-' }}</p>
            <span class="badge {{ $customer->status === 'active' ? 'bg-success' : ($customer->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                {{ ucfirst($customer->status) }}
            </span>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="dashboard-card">
            <h5 class="mb-3">Customer Information</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="text-muted small">Phone Number</label>
                    <p class="mb-0 fw-semibold">{{ $customer->phone ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Join Date</label>
                    <p class="mb-0 fw-semibold">{{ $customer->created_at?->format('Y-m-d') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Current Plan</label>
                    <p class="mb-0"><span class="badge bg-primary">{{ $latestSubscription?->plan?->name ?? $customer->plan ?? '-' }}</span></p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Billing Cycle</label>
                    <p class="mb-0 fw-semibold">{{ ucfirst((string) ($latestSubscription?->billing_cycle ?? $customer->billing_cycle ?? '-')) }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Subscription Date</label>
                    <p class="mb-0 fw-semibold">{{ $latestSubscription?->start_date?->format('Y-m-d') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Expiry Date</label>
                    <p class="mb-0 fw-semibold">{{ ($latestSubscription?->end_date ?? $customer->expiry_date)?->format('Y-m-d') ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Instances</label>
                    <p class="mb-0 fw-semibold">{{ $instances->count() }} / {{ $customer->max_instances }}</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Total Paid</label>
                    <p class="mb-0 fw-semibold text-success">${{ number_format($customerStats['total_paid'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(37, 211, 102, 0.1); color: #25D366;">
                <i class="bi bi-whatsapp"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Active Instances</p>
                <h3 class="mb-0 fw-bold">{{ $customerStats['active_instances'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-hdd-network"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Instances</p>
                <h3 class="mb-0 fw-bold">{{ $customerStats['total_instances'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;">
                <i class="bi bi-credit-card"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Payments</p>
                <h3 class="mb-0 fw-bold">{{ $customerStats['total_payments'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Invoices</p>
                <h3 class="mb-0 fw-bold">{{ $customerStats['total_invoices'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="dashboard-card">
            <h5 class="mb-3">Instances</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instances as $instance)
                            <tr>
                                <td>{{ $instance->name ?: ($instance->label ?: '-') }}</td>
                                <td>{{ $instance->phone_number ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $instance->status === 'connected' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst((string) $instance->status) }}
                                    </span>
                                </td>
                                <td>{{ $instance->created_at?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No instances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="dashboard-card">
            <h5 class="mb-3">Payments</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments->take(10) as $payment)
                            <tr>
                                <td>{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</td>
                                <td>${{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucfirst((string) $payment->method) }}</td>
                                <td>
                                    <span class="badge {{ $payment->status === 'approved' ? 'bg-success' : ($payment->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ ucfirst((string) $payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="dashboard-card">
            <h5 class="mb-3">Invoices</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices->take(10) as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number ?: ('#' . $invoice->id) }}</td>
                                <td>{{ $invoice->issued_at?->format('Y-m-d') ?? '-' }}</td>
                                <td>${{ number_format((float) $invoice->amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst((string) $invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
