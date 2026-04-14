<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum to include 'cancelled' status
        DB::statement("ALTER TABLE customers MODIFY COLUMN status ENUM('active', 'expired', 'pending', 'cancelled') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original values
        DB::statement("ALTER TABLE customers MODIFY COLUMN status ENUM('active', 'expired', 'pending') DEFAULT 'active'");
    }
};
