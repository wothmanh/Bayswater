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
            $table->date('quotation_extraction_date_from')->nullable()->after('valid_to_date');
            $table->date('quotation_extraction_date_to')->nullable()->after('quotation_extraction_date_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_rules', function (Blueprint $table) {
            $table->dropColumn(['quotation_extraction_date_from', 'quotation_extraction_date_to']);
        });
    }
};
