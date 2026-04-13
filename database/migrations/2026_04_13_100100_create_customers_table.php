<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            return;
        }

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable()->index();
            $table->string('country_code', 5)->nullable();
            $table->string('plan')->nullable();
            $table->enum('status', ['active', 'expired', 'pending'])->default('active')->index();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('max_instances')->default(1);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

