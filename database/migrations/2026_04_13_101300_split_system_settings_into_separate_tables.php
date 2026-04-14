<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function (Blueprint $table) {
                $table->id();
                $table->string('site_name')->default('WhatsApp Campaign Platform');
                $table->string('site_url')->nullable();
                $table->string('support_email')->nullable();
                $table->string('support_phone')->nullable();
                $table->text('site_description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_settings')) {
            Schema::create('payment_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('vodafone_cash_enabled')->default(true);
                $table->string('vodafone_cash_number')->nullable();
                $table->boolean('bank_transfer_enabled')->default(true);
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_iban')->nullable();
                $table->boolean('credit_card_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('email_settings')) {
            Schema::create('email_settings', function (Blueprint $table) {
                $table->id();
                $table->string('smtp_host')->nullable();
                $table->unsignedInteger('smtp_port')->nullable();
                $table->string('smtp_username')->nullable();
                $table->string('smtp_password')->nullable();
                $table->string('from_email')->nullable();
                $table->string('from_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_settings')) {
            Schema::create('notification_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('notify_new_customer')->default(true);
                $table->boolean('notify_new_payment')->default(true);
                $table->boolean('notify_expiring')->default(true);
                $table->boolean('notify_expired')->default(true);
                $table->timestamps();
            });
        }

        $legacy = Schema::hasTable('system_settings')
            ? DB::table('system_settings')->where('id', 1)->first()
            : null;

        if (!DB::table('general_settings')->where('id', 1)->exists()) {
            DB::table('general_settings')->insert([
                'id' => 1,
                'site_name' => $legacy->site_name ?? 'WhatsApp Campaign Platform',
                'site_url' => $legacy->site_url ?? config('app.url'),
                'support_email' => $legacy->support_email ?? 'support@example.com',
                'support_phone' => $legacy->support_phone ?? '+20 100 000 0000',
                'site_description' => $legacy->site_description ?? 'WhatsApp marketing automation platform',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('payment_settings')->where('id', 1)->exists()) {
            DB::table('payment_settings')->insert([
                'id' => 1,
                'vodafone_cash_enabled' => $legacy->vodafone_cash_enabled ?? true,
                'vodafone_cash_number' => $legacy->vodafone_cash_number ?? null,
                'bank_transfer_enabled' => $legacy->bank_transfer_enabled ?? true,
                'bank_name' => $legacy->bank_name ?? null,
                'bank_account_number' => $legacy->bank_account_number ?? null,
                'bank_iban' => $legacy->bank_iban ?? null,
                'credit_card_enabled' => $legacy->credit_card_enabled ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('email_settings')->where('id', 1)->exists()) {
            DB::table('email_settings')->insert([
                'id' => 1,
                'smtp_host' => $legacy->smtp_host ?? 'smtp.gmail.com',
                'smtp_port' => $legacy->smtp_port ?? 587,
                'smtp_username' => $legacy->smtp_username ?? 'noreply@example.com',
                'smtp_password' => $legacy->smtp_password ?? '',
                'from_email' => $legacy->from_email ?? 'noreply@example.com',
                'from_name' => $legacy->from_name ?? 'WhatsApp Platform',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('notification_settings')->where('id', 1)->exists()) {
            DB::table('notification_settings')->insert([
                'id' => 1,
                'notify_new_customer' => $legacy->notify_new_customer ?? true,
                'notify_new_payment' => $legacy->notify_new_payment ?? true,
                'notify_expiring' => $legacy->notify_expiring ?? true,
                'notify_expired' => $legacy->notify_expired ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('general_settings');
    }
};
