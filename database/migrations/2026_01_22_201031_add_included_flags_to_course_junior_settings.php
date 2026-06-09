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
        Schema::table('course_junior_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('course_junior_settings', 'includes_registration_fee')) {
                $table->boolean('includes_registration_fee')->default(false);
            }
            if (!Schema::hasColumn('course_junior_settings', 'includes_books_fee')) {
                $table->boolean('includes_books_fee')->default(false);
            }
            // includes_accommodation already exists
            if (!Schema::hasColumn('course_junior_settings', 'includes_accommodation_placement')) {
                $table->boolean('includes_accommodation_placement')->default(false);
            }
            if (!Schema::hasColumn('course_junior_settings', 'includes_activities')) {
                $table->boolean('includes_activities')->default(false);
            }
            if (!Schema::hasColumn('course_junior_settings', 'includes_local_travel')) {
                $table->boolean('includes_local_travel')->default(false);
            }
            if (!Schema::hasColumn('course_junior_settings', 'includes_airport_transfer')) {
                $table->boolean('includes_airport_transfer')->default(false);
            }
            if (!Schema::hasColumn('course_junior_settings', 'includes_insurance')) {
                $table->boolean('includes_insurance')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_junior_settings', function (Blueprint $table) {
            $table->dropColumn([
                'includes_registration_fee',
                'includes_books_fee',
                'includes_accommodation_placement',
                'includes_activities',
                'includes_local_travel',
                'includes_airport_transfer',
                'includes_insurance',
            ]);
        });
    }
};
