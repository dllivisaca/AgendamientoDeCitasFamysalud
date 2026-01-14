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
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending_verification','pending_payment','confirmed','paid','completed','no_show','on_hold','cancelled','rescheduled') NOT NULL");
        DB::statement("ALTER TABLE appointments MODIFY payment_status ENUM('pending','unpaid','partial','paid','refunded','awaiting_validation','rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending_verification','pending_payment','on_hold','confirmed','paid','completed','cancelled','no_show','rescheduled') NOT NULL");
        DB::statement("ALTER TABLE appointments MODIFY payment_status ENUM('pending','awaiting_validation','paid','rejected') NOT NULL DEFAULT 'pending'");
    }
};
