<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'expiring_notified_at')) {
                $table->timestamp('expiring_notified_at')->nullable()->after('billing_cycle');
            }

            if (!Schema::hasColumn('subscriptions', 'expired_notified_at')) {
                $table->timestamp('expired_notified_at')->nullable()->after('expiring_notified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'expired_notified_at')) {
                $table->dropColumn('expired_notified_at');
            }

            if (Schema::hasColumn('subscriptions', 'expiring_notified_at')) {
                $table->dropColumn('expiring_notified_at');
            }
        });
    }
};
