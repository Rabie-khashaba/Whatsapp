@extends('admins.layouts.master')

@section('title', 'Invoices - Admin Panel')
@section('page_title', 'Invoices')
@section('page_subtitle', 'Manage customer invoices')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Invoices</p>
                <h3 class="mb-0 fw-bold">{{ $totalInvoices }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Paid Invoices</p>
                <h3 class="mb-0 fw-bold">{{ $paidInvoices }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Amount</p>
                <h3 class="mb-0 fw-bold text-success">${{ number_format((float) $totalAmount, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3">
    <form method="GET" action="{{ route('admin.invoices') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Invoice number, customer name...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('admin.invoices') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $planName = $invoice->subscription?->plan?->name ?? $invoice->customer?->plan ?? '-';
                        $statusClass = $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning text-dark');
                    @endphp
                    <tr>
                        <td><span class="fw-semibold">{{ $invoice->invoice_number }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $invoice->customer?->name ?? '-' }}</div>
                            <small class="text-muted">{{ $invoice->customer?->email ?? $invoice->customer?->phone ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $planName }}</span>
                        </td>
                        <td class="fw-semibold text-success">${{ number_format((float) $invoice->amount, 2) }}</td>
                        <td>{{ $invoice->issued_at?->format('M j, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $statusClass }}">{{ $invoice->status }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.invoices.show', ['invoice' => $invoice->id, 'print' => 1]) }}" target="_blank" class="btn btn-outline-secondary" title="Print / Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}
        </div>
        <div>{{ $invoices->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
