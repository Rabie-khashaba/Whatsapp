<?php

namespace App\Console\Commands;

use App\Services\SubscriptionNotificationService;
use Illuminate\Console\Command;

class DispatchSubscriptionNotifications extends Command
{
    protected $signature = 'subscriptions:dispatch-notifications';

    protected $description = 'Dispatch WhatsApp notifications for expiring and expired subscriptions';

    public function handle(SubscriptionNotificationService $subscriptionNotificationService): int
    {
        $subscriptionNotificationService->dispatch();

        $this->info('Subscription notifications dispatched successfully.');

        return self::SUCCESS;
    }
}
