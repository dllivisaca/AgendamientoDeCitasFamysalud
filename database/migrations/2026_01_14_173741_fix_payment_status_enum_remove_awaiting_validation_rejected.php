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
        // 1) Normalizar valores antiguos que ya no existirán en el ENUM
        DB::statement("UPDATE appointments SET payment_status = 'pending' WHERE payment_status IN ('awaiting_validation','rejected')");
        // 2) Alterar ENUM dejando solo los permitidos
        DB::statement("ALTER TABLE appointments MODIFY payment_status ENUM('pending','unpaid','partial','paid','refunded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY payment_status ENUM('pending','unpaid','partial','paid','refunded','awaiting_validation','rejected') NOT NULL DEFAULT 'pending'");
    }
};
