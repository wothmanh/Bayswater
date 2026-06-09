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
        Schema::table('airports', function (Blueprint $table) {
            $table->decimal('arrival_price_2026', 8, 2)->nullable()->after('departure_price');
            $table->decimal('departure_price_2026', 8, 2)->nullable()->after('arrival_price_2026');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airports', function (Blueprint $table) {
            $table->dropColumn(['arrival_price_2026', 'departure_price_2026']);
        });
    }
};
