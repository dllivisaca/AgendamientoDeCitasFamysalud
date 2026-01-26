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
        Schema::table('appointment_reschedules', function (Blueprint $table) {
            $table->dateTime('from_end_datetime')->nullable()->after('from_datetime');
            $table->dateTime('to_end_datetime')->nullable()->after('to_datetime');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_reschedules', function (Blueprint $table) {
            $table->dropColumn(['from_end_datetime', 'to_end_datetime']);
        });
    }
};
