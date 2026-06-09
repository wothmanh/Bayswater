<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend enum to include overlapping_duration
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN date_condition_type ENUM('booking_date','start_date','overlapping_duration') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN date_condition_type ENUM('booking_date','start_date') NULL");
    }
};