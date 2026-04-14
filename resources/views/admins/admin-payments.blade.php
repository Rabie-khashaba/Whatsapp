@extends('admins.layouts.master')

@section('title', 'All Payments - Admin Panel')
@section('page_title', 'All Payments')
@section('page_subtitle', 'Complete payment history')

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
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Completed</p>
                <h3 class="mb-0 fw-bold">{{ $totalCompleted }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Revenue</p>
                <h3 class="mb-0 fw-bold text-success">${{ number_format((float) $totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">This Month</p>
                <h3 class="mb-0 fw-bold">${{ number_format((float) $monthlyRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Rejected</p>
                <h3 class="mb-0 fw-bold">{{ $totalRejected }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3">
    <form method="GET" action="{{ route('admin.payments') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Customer name, email...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Method</label>
                <select class="form-select" name="method">
                    <option value="">All</option>
                    <option value="vodafone_cash" {{ request('method') === 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="credit_card" {{ request('method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="manual" {{ request('method') === 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
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
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payments->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->customer->name ?? '-' }}</div>
                            <small class="text-muted">{{ $payment->customer->email ?? $payment->customer->phone ?? '-' }}</small>
                        </td>
                        <td>{{ $payment->subscription?->plan?->name ?? ($payment->customer->plan ?? '-') }}</td>
                        <td>${{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>{{ ($payment->paid_at ?? $payment->created_at)?->format('M j, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning text-dark' : 'danger') }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>
                            @php($invoice = $payment->invoice)
                            @if($invoice)
                                <span class="badge bg-light text-dark">{{ $invoice->invoice_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}
        </div>
        <div>{{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
