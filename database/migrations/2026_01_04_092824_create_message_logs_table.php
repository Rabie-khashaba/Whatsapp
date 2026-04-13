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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            
            // معلومات الـ Instance
            $table->foreignId('instance_id')
                ->nullable()
                ->constrained('instances')
                ->onDelete('cascade');
            
            // معلومات الرسالة
            $table->string('phone', 20)->index();
            $table->text('message');
            
            // حالة الإرسال
            $table->integer('status')->default(0)->index(); // HTTP status code
            $table->json('response')->nullable(); // الرد من Green API
            
            // معلومات إضافية
            $table->string('message_id')->nullable()->index(); // ID من WhatsApp
            $table->enum('type', ['text', 'image', 'video', 'document', 'audio'])->default('text');
            $table->boolean('is_delivered')->default(false)->index();
            $table->boolean('is_read')->default(false);
            
            // للـ monitoring
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Indexes للبحث السريع
            $table->index(['instance_id', 'sent_at']);
            $table->index(['instance_id', 'status']);
            $table->index(['phone', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
