<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
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

        $subscriptions = Subscription::query()
            ->where('customer_id', $customer->id)
            ->with('plan')
            ->orderByDesc('end_date')
            ->get()
            ->map(function (Subscription $subscription) use ($customer) {
                $displayStatus = $this->resolveDisplayStatus($subscription);
                $category = $this->resolveCategory($subscription);
                $billingCycle = $subscription->billing_cycle ?? $subscription->plan?->billing_cycle ?? $customer->billing_cycle ?? 'monthly';

                $subscription->setAttribute('display_status', $displayStatus);
                $subscription->setAttribute('category', $category);
                $subscription->setAttribute('display_period', $billingCycle === 'yearly' ? '1 year' : '1 month');
                $subscription->setAttribute('display_type', strtoupper($subscription->plan?->slug ?? 'plan'));
                $subscription->setAttribute('display_currency', $subscription->plan?->currency ?? 'USD');

                return $subscription;
            });

        $personalSubscriptions = $subscriptions->where('category', 'personal')->values();
        $businessSubscriptions = $subscriptions->where('category', 'business')->values();

        $summary = [
            'total' => $subscriptions->count(),
            'active' => $subscriptions->where('display_status', 'active')->count(),
            'expired' => $subscriptions->where('display_status', 'expired')->count(),
            'pending' => $subscriptions->where('display_status', 'pending')->count(),
            'trial' => $subscriptions->filter(fn (Subscription $subscription) => $this->isTrialPlan($subscription))->count(),
            'business' => $businessSubscriptions->count(),
        ];

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('monthly_price')
            ->get();

        return view('whatsapp.subscriptions', compact(
            'customer',
            'personalSubscriptions',
            'businessSubscriptions',
            'summary',
            'plans'
        ));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'No customer profile found for your account.',
            ]);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = Plan::query()
            ->where('id', $validated['plan_id'])
            ->where('is_active', true)
            ->first();

        if (!$plan) {
            return redirect()->route('subscriptions.index')->withErrors([
                'error' => 'Selected plan is not available right now.',
            ]);
        }

        $price = $validated['billing_cycle'] === 'yearly'
            ? (float) ($plan->yearly_price ?? 0)
            : (float) ($plan->monthly_price ?? $plan->price ?? 0);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'price' => $price,
            'billing_cycle' => $validated['billing_cycle'],
        ]);

        return redirect()->route('payments.index', [
            'subscription' => $subscription->id,
            'checkout' => 1,
        ])->with('success', 'Plan selected. Complete your payment to activate the subscription.');
    }

    private function resolveDisplayStatus(Subscription $subscription): string
    {
        if ($subscription->status === 'active' && $subscription->end_date?->isPast()) {
            return 'expired';
        }

        return $subscription->status;
    }

    private function resolveCategory(Subscription $subscription): string
    {
        $haystack = strtolower(implode(' ', array_filter([
            $subscription->plan?->name,
            $subscription->plan?->slug,
            $subscription->plan?->description,
        ])));

        return str_contains($haystack, 'business') || str_contains($haystack, 'enterprise')
            ? 'business'
            : 'personal';
    }

    private function isTrialPlan(Subscription $subscription): bool
    {
        $haystack = strtolower(implode(' ', array_filter([
            $subscription->plan?->name,
            $subscription->plan?->slug,
            $subscription->plan?->description,
        ])));

        return str_contains($haystack, 'trial');
    }
}
