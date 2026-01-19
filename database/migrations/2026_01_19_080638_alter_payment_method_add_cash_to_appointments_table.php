<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE appointments 
            MODIFY payment_method ENUM('card','transfer','cash') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE appointments 
            MODIFY payment_method ENUM('card','transfer') NOT NULL
        ");
    }
};