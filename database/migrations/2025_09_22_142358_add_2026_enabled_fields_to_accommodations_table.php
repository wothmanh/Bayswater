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
            // Add 2026 enabled checkbox fields for add-ons
            $table->boolean('private_bathroom_enabled_2026')->default(false)->after('dietary_supplement_fee_2026');
            $table->boolean('dietary_supplement_enabled_2026')->default(false)->after('private_bathroom_enabled_2026');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'private_bathroom_enabled_2026',
                'dietary_supplement_enabled_2026'
            ]);
        });
    }
};
