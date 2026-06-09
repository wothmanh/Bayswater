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
            $table->boolean('other_charge_enabled')->default(false)->after('dietary_supplement_enabled_2026');
            $table->string('other_charge_name')->nullable()->after('other_charge_enabled');
            $table->decimal('other_charge_amount', 8, 2)->nullable()->after('other_charge_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'other_charge_enabled',
                'other_charge_name',
                'other_charge_amount',
            ]);
        });
    }
};
