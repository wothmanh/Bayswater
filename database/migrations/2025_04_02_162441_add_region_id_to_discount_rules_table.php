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
        Schema::table('discount_rules', function (Blueprint $table) {
            // Add region_id after country_id (or choose another suitable position)
            $table->foreignId('region_id')
                  ->nullable()
                  ->after('country_id') // Position the column
                  ->constrained('regions') // Foreign key constraint
                  ->nullOnDelete(); // Set region_id to null if the region is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_rules', function (Blueprint $table) {
            // Drop foreign key first (Laravel generates name like discount_rules_region_id_foreign)
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }
};
