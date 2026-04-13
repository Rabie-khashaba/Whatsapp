@extends('admins.layouts.master')

@section('title', 'Active Subscriptions - Admin Panel')
@section('page_title', request('status') === 'expiring' ? 'Expiring Soon' : 'Active Subscriptions')
@section('page_subtitle', 'Manage customer subscriptions')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Active Subscriptions</p>
                <h3 class="mb-0 fw-bold">{{ $activeSubscriptionsCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Expiring Soon</p>
                <h3 class="mb-0 fw-bold">{{ $expiringSoonCount }}</h3>
                <small class="text-warning">Next 7 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Monthly Renewals</p>
                <h3 class="mb-0 fw-bold">{{ $monthlyRenewalsCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">MRR</p>
                <h3 class="mb-0 fw-bold text-success">${{ number_format((float) $mrr, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3">
    <form method="GET" action="{{ route('admin.subscriptions') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Customer name...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Plan</label>
                <select class="form-select" name="plan">
                    <option value="">All</option>
                    @foreach($planNames as $planName)
                        <option value="{{ \Illuminate\Support\Str::lower($planName) }}" {{ request('plan') === \Illuminate\Support\Str::lower($planName) ? 'selected' : '' }}>
                            {{ $planName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Billing Cycle</label>
                <select class="form-select" name="billing_cycle">
                    <option value="">All</option>
                    <option value="monthly" {{ request('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ request('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('admin.subscriptions') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Billing Cycle</th>
                    <th>Start Date</th>
                    <th>Expiry Date</th>
                    <th>Days Left</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    @php
                        $customer = $subscription->customer;
                        $planName = $subscription->plan?->name ?? ($customer->plan ?? 'N/A');
                        $planColor = $subscription->plan?->color ?? 'secondary';
                        $daysLeft = now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($subscription->end_date)->startOfDay(), false);
                        $isExpiring = $subscription->status === 'active' && $daysLeft >= 0 && $daysLeft <= 7;
                        $statusText = $subscription->status;
                        $statusClass = 'secondary';
                        if ($subscription->status === 'active' && $daysLeft < 0) {
                            $statusText = 'expired';
                            $statusClass = 'danger';
                        } elseif ($isExpiring) {
                            $statusText = 'expiring';
                            $statusClass = 'warning text-dark';
                        } elseif ($subscription->status === 'active') {
                            $statusClass = 'success';
                        } elseif ($subscription->status === 'pending') {
                            $statusClass = 'warning text-dark';
                        } elseif ($subscription->status === 'cancelled') {
                            $statusClass = 'secondary';
                        } elseif ($subscription->status === 'expired') {
                            $statusClass = 'danger';
                        }
                    @endphp
                    <tr>
                        <td>{{ $subscriptions->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $customer->name ?? '-' }}</div>
                            <small class="text-muted">{{ $customer->email ?? $customer->phone ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $planColor }}">{{ strtoupper($planName) }}</span>
                        </td>
                        <td>{{ ucfirst($customer->billing_cycle ?? 'monthly') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($subscription->start_date)->format('M j, Y') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($subscription->end_date)->format('M j, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $daysLeft < 0 ? 'danger' : ($daysLeft <= 7 ? 'warning text-dark' : 'success') }}">
                                {{ $daysLeft }} days
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($customer)
                                    <a href="{{ route('admin.page', ['page' => 'customer-details']) }}?id={{ $customer->id }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.subscriptions.renew', $subscription->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Renew" {{ $statusText === 'cancelled' ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" title="Cancel" {{ $statusText === 'cancelled' ? 'disabled' : '' }}>
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $subscriptions->firstItem() ?? 0 }} to {{ $subscriptions->lastItem() ?? 0 }} of {{ $subscriptions->total() }}
        </div>
        <div>
            {{ $subscriptions->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
