@extends('admins.layouts.master')

@section('title', 'Customers Management - Admin Panel')
@section('page_title', 'Customers Management')
@section('page_subtitle', 'Manage all customers and their subscriptions')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-people"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Customers</p>
                <h3 class="mb-0 fw-bold">{{ $totalCustomers }}</h3>
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
                <h3 class="mb-0 fw-bold">{{ $activeCustomers }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Pending</p>
                <h3 class="mb-0 fw-bold">{{ $pendingCustomers }}</h3>
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
                <h3 class="mb-0 fw-bold">{{ $expiredCustomers }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3">
    <form method="GET" action="{{ route('admin.customers') }}">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Name, email, phone...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Plan</label>
                <select class="form-select" name="plan">
                    <option value="">All</option>
                    <option value="Basic" {{ request('plan') === 'Basic' ? 'selected' : '' }}>Basic</option>
                    <option value="Pro" {{ request('plan') === 'Pro' ? 'selected' : '' }}>Pro</option>
                    <option value="Enterprise" {{ request('plan') === 'Enterprise' ? 'selected' : '' }}>Enterprise</option>
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
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-2"></i>Search</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.customers') }}"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
            <button class="btn btn-success ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-person-plus me-2"></i>Add Customer
            </button>
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
                <th>Email</th>
                <th>Phone</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Joined Date</th>
                <th>Expiry Date</th>
                <th>Instances</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td><span class="badge bg-secondary">{{ $customer->plan }}</span></td>
                    <td>
                        <span class="badge {{ $customer->status === 'active' ? 'bg-success' : ($customer->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </td>
                    <td>{{ optional($customer->created_at)->format('Y-m-d') }}</td>
                    <td>{{ optional($customer->expiry_date)->format('Y-m-d') }}</td>
                    <td>{{ $customer->max_instances }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">No customers found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} customers
        </div>
        {{ $customers->links('pagination::bootstrap-5') }}
    </div>
</div>

@foreach($customers as $customer)
<div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status', $customer->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('status', $customer->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Instances</label>
                        <input type="number" class="form-control" name="max_instances" min="1" max="100"
                            value="{{ old('max_instances', $customer->max_instances) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date"
                            value="{{ old('expiry_date', optional($customer->expiry_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave empty to keep current password">
                        <small class="text-muted">Only fill this field if you want to change the customer's password.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country Code</label>
                            <input type="text" class="form-control" name="country_code" placeholder="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plan</label>
                            <select class="form-select" name="plan" required>
                                <option value="">Select Plan</option>
                                <option value="Basic">Basic</option>
                                <option value="Pro">Pro</option>
                                <option value="Enterprise">Enterprise</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Billing Cycle</label>
                            <select class="form-select" name="billing_cycle" required>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Instances</label>
                            <input type="number" class="form-control" name="max_instances" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="expiry_date">
                        </div>
                        <input type="hidden" name="status" value="active">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if (request('action') === 'add')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('addCustomerModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
@endif
@endsection
