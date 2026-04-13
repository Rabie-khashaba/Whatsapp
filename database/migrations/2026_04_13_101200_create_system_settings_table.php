<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_settings')) {
            return;
        }

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('WhatsApp Campaign Platform');
            $table->string('site_url')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('site_description')->nullable();
            $table->boolean('vodafone_cash_enabled')->default(true);
            $table->string('vodafone_cash_number')->nullable();
            $table->boolean('bank_transfer_enabled')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_iban')->nullable();
            $table->boolean('credit_card_enabled')->default(true);
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('notify_new_customer')->default(true);
            $table->boolean('notify_new_payment')->default(true);
            $table->boolean('notify_expiring')->default(true);
            $table->boolean('notify_expired')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
