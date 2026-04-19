<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\FawaterkPaymentService;
use App\Services\SubscriptionStatusService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'No customer profile found for your account.',
            ]);
        }

        $payments = Payment::query()
            ->where('customer_id', $customer->id)
            ->with(['subscription'])
            ->latest()
            ->paginate(10);

        $subscriptions = Subscription::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['active', 'pending'])
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();

        $selectedSubscriptionId = request()->integer('subscription');
        $selectedSubscription = $subscriptions->firstWhere('id', $selectedSubscriptionId);
        $openPaymentModal = request()->boolean('checkout') && $selectedSubscription !== null;
        $fawaterkPaymentMethods = app(FawaterkPaymentService::class)->paymentMethods();

        $totalPayments = Payment::where('customer_id', $customer->id)->count();
        $totalAmount = Payment::where('customer_id', $customer->id)->where('status', 'approved')->sum('amount');
        $verifiedPayments = Payment::where('customer_id', $customer->id)->where('status', 'approved')->count();
        $pendingPayments = Payment::where('customer_id', $customer->id)->where('status', 'pending')->count();

        return view('whatsapp.payments', compact(
            'payments',
            'subscriptions',
            'selectedSubscription',
            'selectedSubscriptionId',
            'openPaymentModal',
            'totalPayments',
            'totalAmount',
            'verifiedPayments',
            'pendingPayments',
            'fawaterkPaymentMethods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'No customer profile found for your account.',
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'in:paymob,fawaterk'],
            'fawaterk_payment_method_id' => ['nullable', 'integer', 'required_if:method,fawaterk'],
            'subscription_id' => ['nullable', 'integer', 'exists:subscriptions,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $method = $validated['method'];

        $subscriptionId = $validated['subscription_id'] ?? null;
        $amount = (float) $validated['amount'];
        $selectedSubscription = null;
        if ($subscriptionId) {
            $selectedSubscription = Subscription::where('id', $subscriptionId)
                ->where('customer_id', $customer->id)
                ->first();

            if (!$selectedSubscription) {
                return redirect()->route('payments.index')->withErrors([
                    'subscription_id' => 'Invalid subscription selected.',
                ])->withInput();
            }

            $amount = (float) $selectedSubscription->price;
        }

        $payment = Payment::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'currency' => 'EGP',
            'method' => $method,
            'status' => 'pending',
            'paid_at' => null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? 'User';

        $paymentRequest = new Request([
            'amount' => $amount,
            'currency' => 'EGP',
            'delivery_needed' => false,
            'items' => [],
            'merchant_order_id' => 'PAY-' . $payment->id,
            'shipping_data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => (string) ($user->phone ?? ''),
                'email' => (string) ($user->email ?? ''),
            ],
            'payment_method_id' => $validated['fawaterk_payment_method_id'] ?? null,
            'item_name' => $selectedSubscription?->plan?->name
                ? 'Subscription: ' . $selectedSubscription->plan->name
                : 'Subscription payment',
        ]);

        $gateway = $method === 'fawaterk'
            ? app(FawaterkPaymentService::class)
            : $this->paymentGateway;

        $response = $gateway->sendPayment($paymentRequest);

        if (($response['success'] ?? false) && !empty($response['url'])) {
            if (!empty($response['provider_order_id'])) {
                $label = $method === 'fawaterk' ? 'Fawaterk invoice: ' : 'Paymob order: ';
                $payment->notes = trim((string) ($payment->notes ? $payment->notes . "\n" : '') . $label . $response['provider_order_id']);
                if (!empty($response['provider_invoice_key'])) {
                    $payment->notes = trim($payment->notes . "\n" . 'Fawaterk invoice key: ' . $response['provider_invoice_key']);
                }
                $payment->save();
            }

            return redirect()->away($response['url']);
        }

        $payment->status = 'rejected';
        $payment->notes = trim((string) ($payment->notes ? $payment->notes . "\n" : '') . ($response['message'] ?? 'Payment link creation failed.'));
        $payment->save();

        return redirect()->route('payment.failed');
    }

    public function paymobCallback(Request $request): RedirectResponse
    {
        $result = $this->paymentGateway->callBack($request);
        $payment = $this->findPaymentFromGatewayResult($result, 'Paymob order: ');

        return $this->handleGatewayResult($payment, $result, 'Paymob');
    }

    public function fawaterkCallback(Request $request): RedirectResponse
    {
        $result = app(FawaterkPaymentService::class)->callBack($request);

        if (($result['hash_valid'] ?? true) === false) {
            return redirect()->route('payments.index')->withErrors([
                'payment' => 'Invalid Fawaterk callback signature.',
            ]);
        }

        $payment = $this->findPaymentFromGatewayResult($result, 'Fawaterk invoice: ');

        return $this->handleGatewayResult($payment, $result, 'Fawaterk');
    }

    private function findPaymentFromGatewayResult(array $result, string $providerNotePrefix): ?Payment
    {
        $paymentId = (int) ($result['order_id'] ?? 0);
        if ($paymentId > 0) {
            $payment = Payment::find($paymentId);
            if ($payment) {
                return $payment;
            }
        }

        $providerOrderId = (string) ($result['provider_order_id'] ?? '');
        if ($providerOrderId !== '') {
            return Payment::query()
                ->where('notes', 'like', '%' . $providerNotePrefix . $providerOrderId . '%')
                ->latest('id')
                ->first();
        }

        return null;
    }

    private function handleGatewayResult(?Payment $payment, array $result, string $providerName): RedirectResponse
    {
        $ok = (bool) ($result['success'] ?? false);
        $pending = ($result['status'] ?? null) === 'pending';

        if ($payment) {
            if ($payment->status !== 'pending') {
                return $payment->status === 'approved'
                    ? redirect()->route('payments.index')->with('success', 'Payment completed successfully.')
                    : redirect()->route('payments.index')->withErrors([
                        'payment' => 'Payment was not completed.',
                    ]);
            }

            if ($pending) {
                return redirect()->route('payments.index')->with('success', 'Payment is pending confirmation.');
            }

            $payment->status = $ok ? 'approved' : 'rejected';
            $payment->paid_at = $ok ? now() : null;

            $providerTransactionId = (string) ($result['provider_transaction_id'] ?? '');
            if ($providerTransactionId !== '') {
                $payment->notes = trim((string) ($payment->notes ? $payment->notes . "\n" : '') . $providerName . ' transaction: ' . $providerTransactionId);
            }

            $paymentMethod = (string) ($result['payment_method'] ?? '');
            if ($paymentMethod !== '') {
                $payment->notes = trim((string) ($payment->notes ? $payment->notes . "\n" : '') . $providerName . ' method: ' . $paymentMethod);
            }

            $declineReason = (string) ($result['decline_reason'] ?? '');
            if (!$ok && $declineReason !== '') {
                $payment->notes = trim((string) ($payment->notes ? $payment->notes . "\n" : '') . $providerName . ' decline reason: ' . $declineReason);
            }

            $payment->save();

            if ($ok) {
                $this->activateSubscriptionForPayment($payment);
            }
        }

        return $ok
            ? redirect()->route('payments.index')->with('success', 'Payment completed successfully.')
            : redirect()->route('payments.index')->withErrors([
                'payment' => 'Payment was not completed.',
            ]);
    }

    private function activateSubscriptionForPayment(Payment $payment): void
    {
        $customer = $payment->customer;
        if (!$customer) {
            return;
        }

        $subscription = $payment->subscription;
        if (!$subscription) {
            $subscription = Subscription::where('customer_id', $customer->id)
                ->latest('end_date')
                ->first();
        }

        if (!$subscription) {
            $plan = Plan::whereRaw('LOWER(name) = ?', [Str::lower((string) $customer->plan)])->first();
            $startDate = Carbon::today();
            $billingCycle = $customer->billing_cycle === 'yearly' ? 'yearly' : 'monthly';
            $endDate = ($billingCycle === 'yearly')
                ? $startDate->copy()->addYear()->subDay()
                : $startDate->copy()->addMonth()->subDay();

            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan?->id,
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => $payment->amount,
                'billing_cycle' => $billingCycle,
            ]);
        } else {
            $billingCycle = $subscription->billing_cycle ?? $customer->billing_cycle ?? 'monthly';
            $startDate = Carbon::parse($subscription->end_date)->isPast()
                ? Carbon::today()
                : Carbon::parse($subscription->end_date)->addDay();

            $endDate = ($billingCycle === 'yearly')
                ? $startDate->copy()->addYear()->subDay()
                : $startDate->copy()->addMonth()->subDay();

            $subscription->update([
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => $payment->amount,
                'billing_cycle' => $billingCycle,
                'expiring_notified_at' => null,
                'expired_notified_at' => null,
            ]);
        }

        if ($subscription->plan) {
            $customer->update([
                'plan' => $subscription->plan->name,
                'billing_cycle' => $subscription->billing_cycle ?? 'monthly',
                'max_instances' => $subscription->plan->max_instances ?? $customer->max_instances,
            ]);
        }

        app(SubscriptionStatusService::class)->syncSubscription($subscription);

        if ($payment->subscription_id !== $subscription->id) {
            $payment->subscription_id = $subscription->id;
            $payment->save();
        }

        if (!Invoice::where('payment_id', $payment->id)->exists()) {
            $nextInvoiceId = (int) (Invoice::max('id') ?? 0) + 1;
            $invoiceNumber = 'INV-' . Carbon::now()->format('Y') . '-' . str_pad((string) $nextInvoiceId, 4, '0', STR_PAD_LEFT);

            Invoice::create([
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'invoice_number' => $invoiceNumber,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => 'paid',
                'issued_at' => Carbon::today()->toDateString(),
                'due_at' => Carbon::today()->toDateString(),
                'paid_at' => Carbon::now(),
            ]);
        }
    }

    public function success(): View
    {
        return view('payment.success');
    }

    public function failed(): View
    {
        return view('payment.failed');
    }

    public function paymentProcess(Request $request)
    {
        return $this->paymentGateway->sendPayment($request);
    }

    public function showPaymentPage(): View
    {
        return view('payment.form');
    }

    public function paymentPageSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'max:10'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $paymentRequest = new Request([
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'delivery_needed' => false,
            'items' => [],
            'merchant_order_id' => 'WEB-' . now()->format('YmdHis'),
            'shipping_data' => [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
            ],
        ]);

        $response = $this->paymentGateway->sendPayment($paymentRequest);

        if (($response['success'] ?? false) && !empty($response['url'])) {
            return redirect()->away($response['url']);
        }

        return back()->withInput()->withErrors([
            'payment' => 'تعذر إنشاء رابط الدفع. راجع إعدادات Paymob أو بيانات الطلب وحاول مرة أخرى.',
        ]);
    }
}
