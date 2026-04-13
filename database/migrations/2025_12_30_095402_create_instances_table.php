<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('access_token')->nullable();
            $table->string('status')->default('initializing'); // initializing, connected, disconnected
            $table->text('qrcode')->nullable();
            
            // Green API Credentials
            $table->string('green_instance_id')->nullable();
            $table->string('green_api_token')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
