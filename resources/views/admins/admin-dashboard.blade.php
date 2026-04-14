@extends('admins.layouts.master')

@section('title', 'Admin Dashboard - WhatsApp Campaign Platform')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Welcome back, Admin!')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-people"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Customers</p>
                <h3 class="mb-0 fw-bold">{{ $totalCustomers ?? 0 }}</h3>
                <small class="text-success"><i class="bi bi-arrow-up"></i> +12% this month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Active</p>
                <h3 class="mb-0 fw-bold">{{ $activeCustomers ?? 0 }}</h3>
                <small class="text-muted">91% of total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                <i class="bi bi-person-x"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Expired</p>
                <h3 class="mb-0 fw-bold">{{ $expiredCustomers ?? 0 }}</h3>
                <small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Needs attention</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Monthly Revenue</p>
                <h3 class="mb-0 fw-bold">${{ number_format((float) ($monthlyRevenue ?? 0), 2) }}</h3>
                <small class="text-success"><i class="bi bi-arrow-up"></i> +18% vs last month</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Pending Payments</p>
                <h3 class="mb-0 fw-bold">{{ $pendingPayments ?? 0 }}</h3>
                <a href="{{ route('admin.page', ['page' => 'payments-queue']) }}" class="small text-primary">Review now →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(236, 72, 153, 0.1); color: #EC4899;">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Expiring Soon</p>
                <h3 class="mb-0 fw-bold">{{ $expiringSoon ?? 0 }}</h3>
                <small class="text-muted">Next 7 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(6, 182, 212, 0.1); color: #06B6D4;">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Revenue</p>
                <h3 class="mb-0 fw-bold">${{ number_format((float) ($totalRevenue ?? 0), 2) }}</h3>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recent Customers</h5>
                <a href="{{ route('admin.customers') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($recentCustomers ?? collect()) as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}" width="32" height="32" class="rounded-circle">
                                    <div>
                                        <div class="fw-semibold">{{ $customer->name }}</div>
                                        <small class="text-muted">{{ $customer->phone }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge {{ strtolower((string) $customer->plan) === 'pro' ? 'bg-primary' : 'bg-secondary' }}">{{ $customer->plan }}</span></td>
                            <td><span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst((string) $customer->status) }}</span></td>
                            <td>{{ optional($customer->created_at)->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.page', ['page' => 'customer-details']) }}?id={{ $customer->id }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-card">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="d-grid gap-2">
                <button class="btn btn-primary" onclick="window.location.href='{{ route('admin.customers') }}?action=add'"><i class="bi bi-person-plus me-2"></i>Add New Customer</button>
                <button class="btn btn-outline-primary" onclick="window.location.href='{{ route('admin.page', ['page' => 'payments-queue']) }}'"><i class="bi bi-credit-card me-2"></i>Review Payments</button>
                <button class="btn btn-outline-primary" onclick="window.location.href='{{ route('admin.page', ['page' => 'reports']) }}'"><i class="bi bi-graph-up me-2"></i>View Reports</button>
                <button class="btn btn-outline-primary" onclick="window.location.href='{{ route('admin.page', ['page' => 'settings']) }}'"><i class="bi bi-gear me-2"></i>System Settings</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
