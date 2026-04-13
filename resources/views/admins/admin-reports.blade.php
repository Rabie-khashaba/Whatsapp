@extends('admins.layouts.master')

@section('title', 'Reports & Analytics - Admin Panel')
@section('page_title', 'Reports & Analytics')
@section('page_subtitle', 'Business insights and statistics')

@section('content')
<div class="dashboard-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">Reports & Analytics</h4>
            <p class="text-muted mb-0 small">Business insights and statistics</p>
        </div>
        <a href="{{ route('admin.reports.export', ['period' => $period, 'date_from' => $dateFrom->toDateString(), 'date_to' => $dateTo->toDateString()]) }}" class="btn btn-primary">
            <i class="bi bi-download me-2"></i>Export Report
        </a>
    </div>
</div>

<div class="dashboard-card mb-4">
    <form method="GET" action="{{ route('admin.reports') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Time Period</label>
                <select class="form-select" name="period">
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="last_year" {{ $period === 'last_year' ? 'selected' : '' }}>Last Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ $dateTo->toDateString() }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="bi bi-arrow-clockwise me-2"></i>Update</button>
            </div>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;"><i class="bi bi-people"></i></div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Customers</p>
                <h3 class="mb-0 fw-bold">{{ $totalCustomers }}</h3>
                <small class="{{ $customerGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $customerGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                    {{ $customerGrowth }}% vs last period
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;"><i class="bi bi-person-check"></i></div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Active Customers</p>
                <h3 class="mb-0 fw-bold">{{ $activeCustomers }}</h3>
                <small class="text-muted">{{ $totalCustomers > 0 ? round(($activeCustomers / max(1, $totalCustomers)) * 100) : 0 }}% of period total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;"><i class="bi bi-person-x"></i></div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Expired</p>
                <h3 class="mb-0 fw-bold">{{ $expiredCustomers }}</h3>
                <small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Needs attention</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;"><i class="bi bi-cash-stack"></i></div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Revenue</p>
                <h3 class="mb-0 fw-bold text-success">${{ number_format($totalRevenue, 2) }}</h3>
                <small class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="bi {{ $revenueGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                    {{ $revenueGrowth }}% vs last period
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="dashboard-card">
            <h5 class="mb-3">Monthly Revenue Trend</h5>
            @if($monthlyRevenue->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-graph-up fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No revenue data found for the selected period.</p>
                </div>
            @else
                <div class="vstack gap-3">
                    @foreach($monthlyRevenue as $point)
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $point['label'] }}</span>
                                <strong>${{ number_format($point['total'], 2) }}</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-info" style="width: {{ round(($point['total'] / $maxRevenuePoint) * 100, 1) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-card">
            <h5 class="mb-3">Plan Distribution</h5>
            @forelse($planDistribution as $item)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $item['plan'] }}</span>
                        <span class="fw-bold">{{ $item['total'] }} ({{ $item['percentage'] }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $item['color'] }}" style="width: {{ $item['percentage'] }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No plan distribution data available.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="dashboard-card">
    <h5 class="mb-3">Top Customers by Revenue</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Total Paid</th>
                    <th>Last Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $index => $entry)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $entry->customer?->name ?? '-' }}</td>
                        <td><span class="badge bg-primary">{{ $entry->customer?->plan ?? '-' }}</span></td>
                        <td class="fw-bold text-success">${{ number_format((float) $entry->total_paid, 2) }}</td>
                        <td>{{ $entry->last_payment_at ? \Illuminate\Support\Carbon::parse($entry->last_payment_at)->format('M j, Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No payment data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
