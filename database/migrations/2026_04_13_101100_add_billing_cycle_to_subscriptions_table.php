<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions') || Schema::hasColumn('subscriptions', 'billing_cycle')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'yearly'])
                ->default('monthly')
                ->after('price');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriptions') || !Schema::hasColumn('subscriptions', 'billing_cycle')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
    }
};
