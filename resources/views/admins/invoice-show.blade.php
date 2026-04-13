<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }} - Invoice</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f6f8fb;
            font-family: Inter, Arial, sans-serif;
            color: #1f2937;
        }
        .invoice-shell {
            max-width: 980px;
            margin: 32px auto;
            padding: 0 16px;
        }
        .invoice-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }
        .invoice-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .invoice-header {
            padding: 28px;
            background: linear-gradient(135deg, #25D366 0%, #0f766e 100%);
            color: #fff;
        }
        .invoice-body {
            padding: 28px;
        }
        .invoice-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .invoice-box {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 18px;
            background: #f9fafb;
        }
        .invoice-box h6 {
            margin-bottom: 10px;
            color: #6b7280;
        }
        .summary-table td {
            padding: 10px 0;
        }
        @media print {
            body { background: #fff; }
            .invoice-actions { display: none !important; }
            .invoice-shell { max-width: 100%; margin: 0; padding: 0; }
            .invoice-card { box-shadow: none; border: 0; }
        }
        @media (max-width: 768px) {
            .invoice-grid { grid-template-columns: 1fr; }
            .invoice-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="invoice-shell">
        <div class="invoice-actions">
            <div>
                <a href="{{ route('admin.invoices') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to invoices
                </a>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print / Save PDF
                </button>
            </div>
        </div>

        <div class="invoice-card">
            <div class="invoice-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h2 class="mb-2">Invoice</h2>
                    <p class="mb-0 opacity-75">WhatsApp Campaign Platform</p>
                </div>
                <div class="text-end">
                    <div class="fs-4 fw-bold">{{ $invoice->invoice_number }}</div>
                    <span class="badge bg-light text-dark mt-2">{{ strtoupper($invoice->status) }}</span>
                </div>
            </div>

            <div class="invoice-body">
                <div class="invoice-grid">
                    <div class="invoice-box">
                        <h6>Billed To</h6>
                        <div class="fw-semibold">{{ $invoice->customer?->name ?? '-' }}</div>
                        <div>{{ $invoice->customer?->email ?? '-' }}</div>
                        <div>{{ $invoice->customer?->phone ?? '-' }}</div>
                    </div>
                    <div class="invoice-box">
                        <h6>Invoice Details</h6>
                        <div><strong>Issued:</strong> {{ $invoice->issued_at?->format('M j, Y') }}</div>
                        <div><strong>Due:</strong> {{ $invoice->due_at?->format('M j, Y') ?? '-' }}</div>
                        <div><strong>Paid At:</strong> {{ $invoice->paid_at?->format('M j, Y h:i A') ?? '-' }}</div>
                        <div><strong>Payment Method:</strong> {{ str_replace('_', ' ', $invoice->payment?->method ?? '-') }}</div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th>Plan</th>
                                <th>Billing</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>WhatsApp Campaign Platform Subscription</td>
                                <td>{{ $invoice->subscription?->plan?->name ?? $invoice->customer?->plan ?? '-' }}</td>
                                <td>{{ ucfirst($invoice->subscription?->billing_cycle ?? $invoice->customer?->billing_cycle ?? 'monthly') }}</td>
                                <td class="text-end fw-semibold">${{ number_format((float) $invoice->amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table summary-table mb-0">
                            <tr>
                                <td class="text-muted">Subtotal</td>
                                <td class="text-end">${{ number_format((float) $invoice->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tax</td>
                                <td class="text-end">$0.00</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-success">${{ number_format((float) $invoice->amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-top text-muted small">
                    This is a computer-generated invoice and does not require a signature.
                </div>
            </div>
        </div>
    </div>

    @if($isPrintMode)
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
    @endif
</body>
</html>
