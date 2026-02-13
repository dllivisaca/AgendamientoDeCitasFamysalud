<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Modalidades (puede ser una o ambas)
            $table->boolean('is_presential')->default(false)->after('status');
            $table->boolean('is_virtual')->default(false)->after('is_presential');

            // Dirección obligatoria SOLO si es presencial
            $table->unsignedBigInteger('address_id')->nullable()->after('is_virtual');

            // FK opcional (si tu tabla addresses usa id bigint)
            $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn(['is_presential','is_virtual','address_id']);
        });
    }
};
