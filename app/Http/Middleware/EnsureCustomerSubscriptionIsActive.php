<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Services\SubscriptionStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->type === 'admin') {
            return $next($request);
        }

        $customer = $user->customer;

        if (!$customer instanceof Customer) {
            return $this->deny($request, 'No customer profile found for your account.');
        }

        $customer = app(SubscriptionStatusService::class)->syncCustomer($customer);

        if ($customer->expiry_date && $customer->expiry_date->isPast()) {
            return $this->deny($request, 'Your subscription has expired. Please renew your subscription, then try again.');
        }

        if ($customer->status === 'expired') {
            return $this->deny($request, 'Your subscription has expired. Please renew your subscription, then try again.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->routeIs('instance.send')) {
            return response()->json([
                'error' => $message,
                'renew_url' => route('subscriptions.index'),
            ], 403);
        }

        return redirect()
            ->route('subscriptions.index')
            ->withErrors(['subscription' => $message]);
    }
}
