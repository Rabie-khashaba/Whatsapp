<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

class SubscriptionStatusService
{
    public function syncAll(): void
    {
        Customer::query()
            ->chunkById(100, function ($customers): void {
                foreach ($customers as $customer) {
                    $this->syncCustomer($customer);
                }
            });
    }

    public function syncSubscription(Subscription $subscription): ?Customer
    {
        $customer = $subscription->customer;

        if (!$customer) {
            return null;
        }

        return $this->syncCustomer($customer);
    }

    public function syncCustomer(Customer $customer): Customer
    {
        $today = Carbon::today();
        $customer->subscriptions()
            ->where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 'expired']);

        $freshSubscriptions = $customer->subscriptions()
            ->latest('end_date')
            ->get();

        $activeSubscription = $freshSubscriptions->first(
            fn (Subscription $subscription) => $subscription->status === 'active'
                && $subscription->end_date
                && !$subscription->end_date->lt($today)
        );

        $cancelledSubscription = $freshSubscriptions->first(
            fn (Subscription $subscription) => $subscription->status === 'cancelled'
        );

        if ($activeSubscription) {
            $updates = [];

            if ($customer->status !== 'active') {
                $updates['status'] = 'active';
            }

            if (!$customer->expiry_date || !$customer->expiry_date->isSameDay($activeSubscription->end_date)) {
                $updates['expiry_date'] = $activeSubscription->end_date;
            }

            if (!empty($updates)) {
                $customer->update($updates);
                $customer->refresh();
            }

            return $customer;
        }

        // If there's a cancelled subscription, customer should be cancelled
        if ($cancelledSubscription) {
            $updates = [];

            if ($customer->status !== 'cancelled') {
                $updates['status'] = 'cancelled';
            }

            if (!$customer->expiry_date || !$customer->expiry_date->isSameDay($cancelledSubscription->end_date)) {
                $updates['expiry_date'] = $cancelledSubscription->end_date;
            }

            if (!empty($updates)) {
                $customer->update($updates);
                $customer->refresh();
            }

            return $customer;
        }

        $latestSubscription = $freshSubscriptions->first();
        $shouldExpire = ($customer->expiry_date && $customer->expiry_date->lt($today))
            || ($latestSubscription && $latestSubscription->end_date && $latestSubscription->end_date->lt($today));

        if ($shouldExpire) {
            $updates = [];

            if ($customer->status !== 'expired') {
                $updates['status'] = 'expired';
            }

            if ($latestSubscription && $latestSubscription->end_date && (!$customer->expiry_date || !$customer->expiry_date->isSameDay($latestSubscription->end_date))) {
                $updates['expiry_date'] = $latestSubscription->end_date;
            }

            if (!empty($updates)) {
                $customer->update($updates);
                $customer->refresh();
            }
        }

        return $customer;
    }
}
