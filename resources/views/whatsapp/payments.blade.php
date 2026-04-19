@extends('layouts.master')

@section('title', 'Payments - WhatsApp Campaign Platform')
@section('page-title', 'Payments')
@section('page-title-ar', 'المدفوعات')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Error:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="dashboard-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-1">Payments</h4>
        <p class="text-muted mb-0 small">View all your subscription payments</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
        <i class="bi bi-plus-circle me-2"></i>Submit Payment
    </button>
</div>

@if($selectedSubscription)
    <div class="dashboard-card mb-3 border border-success-subtle">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h5 class="mb-1">Selected Plan</h5>
                <p class="mb-1">
                    <strong>{{ $selectedSubscription->plan?->name ?? 'Plan' }}</strong>
                    <span class="badge bg-warning text-dark ms-2">{{ ucfirst($selectedSubscription->billing_cycle ?? 'monthly') }}</span>
                </p>
                <p class="text-muted mb-0 small">
                    Amount due: EGP {{ number_format((float) $selectedSubscription->price, 2) }}
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
                <i class="bi bi-credit-card me-2"></i>Pay Now
            </button>
        </div>
    </div>
@endif

<div class="dashboard-card mb-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>TRANSACTION ID</th>
                    <th>AMOUNT</th>
                    <th>PAYMENT METHOD</th>
                    <th>PAYMENT DATE</th>
                    <th>STATUS</th>
                    <th>NOTES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><code>TX-{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</code></td>
                        <td><strong>EGP {{ number_format((float) $payment->amount, 2) }}</strong></td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>{{ ($payment->paid_at ?? $payment->created_at)?->format('M j, Y h:i A') }}</td>
                        <td>
                            <span class="badge bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning text-dark' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $payment->notes ?: '-' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No payments yet.</td>
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

<div class="row g-3">
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small">Total Payments</p>
                    <h4 class="mb-0 fw-bold">{{ $totalPayments }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small">Total Approved</p>
                    <h4 class="mb-0 fw-bold">EGP {{ number_format((float) $totalAmount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stats-icon" style="background: rgba(34, 197, 94, 0.1); color: #22C55E;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small">Verified Payments</p>
                    <h4 class="mb-0 fw-bold">{{ $verifiedPayments }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stats-icon" style="background: rgba(251, 191, 36, 0.1); color: #FBBF24;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 small">Pending</p>
                    <h4 class="mb-0 fw-bold">{{ $pendingPayments }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="submitPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (EGP)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $selectedSubscription ? (float) $selectedSubscription->price : old('amount') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <div class="d-grid gap-2">
                            <label class="border rounded p-3 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0 payment-gateway-option" type="radio" name="method" value="paymob" {{ old('method', 'paymob') === 'paymob' ? 'checked' : '' }}>
                                <span>Paymob (Credit/Debit Card)</span>
                            </label>
                            <label class="border rounded p-3 d-flex align-items-center gap-2">
                                <input class="form-check-input m-0 payment-gateway-option" type="radio" name="method" value="fawaterk" {{ old('method') === 'fawaterk' ? 'checked' : '' }}>
                                <span>Fawaterk</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="fawaterkMethodWrap">
                        <label class="form-label">Fawaterk Method</label>
                        <select class="form-select" name="fawaterk_payment_method_id" id="fawaterkPaymentMethod">
                            <option value="">Choose Fawaterk method</option>
                            @foreach($fawaterkPaymentMethods as $method)
                                <option value="{{ $method['paymentId'] ?? '' }}" {{ (string) old('fawaterk_payment_method_id') === (string) ($method['paymentId'] ?? '') ? 'selected' : '' }}>
                                    {{ $method['name_en'] ?? $method['name_ar'] ?? 'Payment method' }}
                                    @if(!empty($method['name_ar']))
                                        - {{ $method['name_ar'] }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if(empty($fawaterkPaymentMethods))
                            <small class="text-muted">Add FAWATERK_API_KEY to show enabled Fawaterk methods.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subscription</label>
                        <select class="form-select" name="subscription_id">
                            <option value="">General payment</option>
                            @foreach($subscriptions as $subscription)
                                <option value="{{ $subscription->id }}" {{ (string) old('subscription_id', $selectedSubscriptionId) === (string) $subscription->id ? 'selected' : '' }}>
                                    #{{ $subscription->id }} - {{ $subscription->plan?->name ?? 'Plan' }} ({{ ucfirst($subscription->billing_cycle ?? 'monthly') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Transaction reference, transfer note, etc...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const gatewayOptions = document.querySelectorAll('.payment-gateway-option');
        const fawaterkWrap = document.getElementById('fawaterkMethodWrap');
        const fawaterkSelect = document.getElementById('fawaterkPaymentMethod');

        function syncGatewayFields() {
            const selected = document.querySelector('.payment-gateway-option:checked')?.value || 'paymob';
            const isFawaterk = selected === 'fawaterk';

            if (fawaterkWrap) {
                fawaterkWrap.classList.toggle('d-none', !isFawaterk);
            }

            if (fawaterkSelect) {
                fawaterkSelect.required = isFawaterk;
            }
        }

        gatewayOptions.forEach(function (option) {
            option.addEventListener('change', syncGatewayFields);
        });

        syncGatewayFields();
    });
</script>
@if($openPaymentModal)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('submitPaymentModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    });
</script>
@endif
@endsection
