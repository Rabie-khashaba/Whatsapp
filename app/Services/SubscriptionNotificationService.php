<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\NotificationSetting;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionNotificationService
{
    public function dispatch(): void
    {
        $settings = NotificationSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'notify_new_customer' => true,
                'notify_new_payment' => true,
                'notify_expiring' => true,
                'notify_expired' => true,
            ]
        );

        if ($settings->notify_expiring) {
            $this->sendExpiringSoonNotifications();
        }

        if ($settings->notify_expired) {
            $this->sendExpiredNotifications();
        }
    }

    private function sendExpiringSoonNotifications(): void
    {
        $targetDate = Carbon::today()->addDays(3);

        Subscription::query()
            ->with(['customer.user', 'plan'])
            ->where('status', 'active')
            ->whereDate('end_date', $targetDate)
            ->whereNull('expiring_notified_at')
            ->chunkById(100, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    $this->sendExpiringSoonNotification($subscription);
                }
            });
    }

    private function sendExpiredNotifications(): void
    {
        Subscription::query()
            ->with(['customer.user', 'plan'])
            ->where('status', 'expired')
            ->whereNull('expired_notified_at')
            ->chunkById(100, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    $this->sendExpiredNotification($subscription);
                }
            });
    }

    private function sendExpiringSoonNotification(Subscription $subscription): void
    {
        $customer = $subscription->customer;

        if (!$customer || !$this->canSendToCustomer($subscription)) {
            return;
        }

        $daysLeft = Carbon::today()->diffInDays($subscription->end_date, false);
        $planName = $subscription->plan?->name ?? $customer->plan ?? 'your current plan';
        $message = "Hello {$customer->name}\n"
            . "Your subscription for {$planName} will expire in {$daysLeft} days on {$subscription->end_date->format('Y-m-d')}.\n"
            . "Please renew it in time to avoid interruption.\n"
            . "Renew now: " . route('subscriptions.index');

        if ($this->sendWhatsAppMessage($subscription, $customer->phone, $message)) {
            $subscription->update(['expiring_notified_at' => now()]);
        }
    }

    private function sendExpiredNotification(Subscription $subscription): void
    {
        $customer = $subscription->customer;

        if (!$customer || !$this->canSendToCustomer($subscription)) {
            return;
        }

        $planName = $subscription->plan?->name ?? $customer->plan ?? 'your current plan';
        $message = "Hello {$customer->name}\n"
            . "Your subscription for {$planName} expired on {$subscription->end_date->format('Y-m-d')}.\n"
            . "Sending is currently disabled until renewal.\n"
            . "Renew now: " . route('subscriptions.index');

        if ($this->sendWhatsAppMessage($subscription, $customer->phone, $message)) {
            $subscription->update(['expired_notified_at' => now()]);
        }
    }

    private function canSendToCustomer(Subscription $subscription): bool
    {
        $customer = $subscription->customer;

        if (!$customer || blank($customer->phone)) {
            return false;
        }

        $userSettings = $customer->user?->settings ?? [];

        return (bool) ($userSettings['whatsapp_notifications'] ?? true);
    }

    private function sendWhatsAppMessage(Subscription $subscription, string $phone, string $message): bool
    {
        try {
            $normalizedPhone = PhoneHelper::normalizeEgyptPhone($phone);
            $response = app(WhatsAppService::class)->sendUsingAccessToken(
                config('services.whatsapp.access_token'),
                $normalizedPhone,
                $message
            );

            if ($response['success'] ?? false) {
                return true;
            }

            Log::warning('Subscription notification send failed', [
                'subscription_id' => $subscription->id,
                'phone' => $phone,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('Subscription notification exception', [
                'subscription_id' => $subscription->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }
}
