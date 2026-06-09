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
        Schema::table('schools', function (Blueprint $table) {
            $table->date('christmas_start_date_2026')->nullable()->after('christmas_end_date');
            $table->date('christmas_end_date_2026')->nullable()->after('christmas_start_date_2026');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['christmas_start_date_2026', 'christmas_end_date_2026']);
        });
    }
};
