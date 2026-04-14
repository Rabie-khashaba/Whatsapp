<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
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
            'pendingPayments'
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
            'method' => ['required', 'string', 'max:50'],
            'subscription_id' => ['nullable', 'integer', 'exists:subscriptions,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $subscriptionId = $validated['subscription_id'] ?? null;
        $amount = (float) $validated['amount'];
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

        Payment::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'currency' => 'USD',
            'method' => $validated['method'],
            'status' => 'pending',
            'paid_at' => null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('payments.index')->with('success', 'Payment submitted successfully and waiting for admin approval.');
    }
}
