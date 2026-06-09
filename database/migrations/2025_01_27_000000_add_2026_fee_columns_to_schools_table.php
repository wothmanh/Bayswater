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
            // Add missing 2026 versions of fee columns
            if (!Schema::hasColumn('schools', 'books_weeks_2026')) {
                $table->unsignedInteger('books_weeks_2026')->nullable()->after('books_weeks');
            }
            if (!Schema::hasColumn('schools', 'christmas_fee_per_week_2026')) {
                $table->decimal('christmas_fee_per_week_2026', 8, 2)->nullable()->after('christmas_fee_per_week');
            }
            if (!Schema::hasColumn('schools', 'summer_fee_per_week_2026')) {
                $table->decimal('summer_fee_per_week_2026', 8, 2)->nullable()->after('summer_fee_per_week');
            }
            if (!Schema::hasColumn('schools', 'summer_fee_weeks_off_2026')) {
                $table->unsignedInteger('summer_fee_weeks_off_2026')->nullable()->after('summer_fee_weeks_off');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'registration_fee_2026',
                'accommodation_fee_2026',
                'bank_charges_2026',
                'courier_fee_2026',
                'insurance_fee_per_week_2026',
                'books_fee_2026',
                'guardianship_fee_per_week_2026',
                'custodianship_fee_2026',
                'christmas_supplement_per_week_2026',
                'christmas_supplement_start_date_2026',
                'christmas_supplement_end_date_2026',
                'christmas_extra_accommodation_weeks_2026',
            ]);
        });
    }
};