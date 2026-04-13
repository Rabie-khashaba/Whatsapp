@extends('admins.layouts.master')

@section('title', 'Pending Payments - Admin Panel')
@section('page_title', 'Pending Payments')
@section('page_subtitle', 'Review and approve customer payments')

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
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Pending</p>
                <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Amount</p>
                <h3 class="mb-0 fw-bold">${{ number_format((float) $pendingAmount, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Vodafone Cash</p>
                <h3 class="mb-0 fw-bold">{{ $vodafoneCount }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;">
                <i class="bi bi-bank"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Bank Transfer</p>
                <h3 class="mb-0 fw-bold">{{ $bankCount }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3">
    <form method="GET" action="{{ route('admin.payments.queue') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Customer name, email, phone...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="method">
                    <option value="">All Methods</option>
                    <option value="vodafone_cash" {{ request('method') === 'vodafone_cash' ? 'selected' : '' }}>Vodafone Cash</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="credit_card" {{ request('method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="manual" {{ request('method') === 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('admin.payments.queue') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
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
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Subscription</th>
                    <th>Submitted</th>
                    <th>Notes</th>
                    <th>Actions</th>
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
                        <td>${{ number_format((float) $payment->amount, 2) }}</td>
                        <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $payment->method) }}</span></td>
                        <td>{{ $payment->subscription?->plan?->name ?? ($payment->customer->plan ?? '-') }}</td>
                        <td>{{ $payment->created_at?->format('M j, Y h:i A') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($payment->notes, 60) }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}">
                                    @csrf
                                    <input type="hidden" name="reason" value="Rejected by admin">
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No pending payments found.</td>
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

