<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'monthly_price')) {
                $table->decimal('monthly_price', 10, 2)->default(0)->after('billing_cycle');
            }

            if (!Schema::hasColumn('plans', 'yearly_price')) {
                $table->decimal('yearly_price', 10, 2)->default(0)->after('monthly_price');
            }

            if (!Schema::hasColumn('plans', 'max_messages')) {
                $table->unsignedInteger('max_messages')->default(0)->after('max_instances');
            }

            if (!Schema::hasColumn('plans', 'max_campaigns')) {
                $table->unsignedInteger('max_campaigns')->default(0)->after('max_messages');
            }

            if (!Schema::hasColumn('plans', 'color')) {
                $table->string('color', 20)->default('secondary')->after('max_campaigns');
            }

            if (!Schema::hasColumn('plans', 'description')) {
                $table->text('description')->nullable()->after('color');
            }

            if (!Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            foreach (['features', 'description', 'color', 'max_campaigns', 'max_messages', 'yearly_price', 'monthly_price'] as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

