<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->integer('guardianship_fee_age')->nullable()->after('guardianship_fee_per_week_2026');
            $table->integer('custodianship_fee_age')->nullable()->after('custodianship_fee_2026');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['guardianship_fee_age', 'custodianship_fee_age']);
        });
    }
};