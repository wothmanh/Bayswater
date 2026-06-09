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
        Schema::table('accommodations', function (Blueprint $table) {
            // Summer supplement 2026 fields
            $table->decimal('summer_fee_per_week_2026', 8, 2)->nullable()->after('summer_fee_note');
            $table->date('summer_start_date_2026')->nullable()->after('summer_fee_per_week_2026');
            $table->date('summer_end_date_2026')->nullable()->after('summer_start_date_2026');
            $table->string('summer_fee_note_2026')->nullable()->after('summer_end_date_2026');
            
            // Add-on pricing 2026 fields
            $table->decimal('private_bathroom_fee_2026', 8, 2)->nullable()->after('summer_fee_note_2026');
            $table->decimal('dietary_supplement_fee_2026', 8, 2)->nullable()->after('private_bathroom_fee_2026');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'summer_fee_per_week_2026',
                'summer_start_date_2026',
                'summer_end_date_2026',
                'summer_fee_note_2026',
                'private_bathroom_fee_2026',
                'dietary_supplement_fee_2026'
            ]);
        });
    }
};
