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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('search_accommodation_tab_title')->nullable()->after('market_discount_iframe_url');
            $table->text('search_accommodation_page_link')->nullable()->after('search_accommodation_tab_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['search_accommodation_tab_title', 'search_accommodation_page_link']);
        });
    }
};
