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
        // Change plan column from enum to string
        DB::statement("ALTER TABLE customers MODIFY COLUMN plan VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert plan column back to enum
        DB::statement("ALTER TABLE customers MODIFY COLUMN plan ENUM('basic','pro','enterprise') NULL");
    }
};
