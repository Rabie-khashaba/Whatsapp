<?php

namespace App\Console\Commands;

use App\Services\SubscriptionStatusService;
use Illuminate\Console\Command;

class SyncSubscriptionStatuses extends Command
{
    protected $signature = 'subscriptions:sync-statuses';

    protected $description = 'Sync subscription expiry with customer status and expiry dates';

    public function handle(SubscriptionStatusService $subscriptionStatusService): int
    {
        $subscriptionStatusService->syncAll();

        $this->info('Subscription and customer statuses synced successfully.');

        return self::SUCCESS;
    }
}
