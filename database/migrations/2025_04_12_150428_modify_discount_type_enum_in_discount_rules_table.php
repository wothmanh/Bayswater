<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL for reliable enum modification
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN discount_type ENUM('percentage', 'fixed_amount', 'fee_waiver', 'fixed_amount_per_week') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum definition
        // Important: Ensure any rules using 'fixed_amount_per_week' are handled before rollback
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN discount_type ENUM('percentage', 'fixed_amount', 'fee_waiver') NOT NULL");
    }
};
