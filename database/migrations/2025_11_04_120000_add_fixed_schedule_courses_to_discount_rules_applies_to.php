<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend ENUM to include 'fixed_schedule_courses'
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN applies_to ENUM('course_tuition','accommodation_price','registration_fee','accommodation_fee','addon','fixed_schedule_courses') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM to previous set (without 'fixed_schedule_courses')
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN applies_to ENUM('course_tuition','accommodation_price','registration_fee','accommodation_fee','addon') NOT NULL");
    }
};