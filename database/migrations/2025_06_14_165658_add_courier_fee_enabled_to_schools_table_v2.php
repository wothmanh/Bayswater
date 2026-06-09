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
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('schools', 'courier_fee_enabled')) {
                $table->boolean('courier_fee_enabled')->default(false)->after('courier_fee')->comment('Enable/disable courier fee option for this school');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'courier_fee_enabled')) {
                $table->dropColumn('courier_fee_enabled');
            }
        });
    }
};