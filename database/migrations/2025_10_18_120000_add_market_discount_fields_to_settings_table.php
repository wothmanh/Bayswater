<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'market_discount_tab_title')) {
                $table->string('market_discount_tab_title')->nullable()->after('quotation_extraction_date');
            }
            if (!Schema::hasColumn('settings', 'market_discount_iframe_url')) {
                $table->string('market_discount_iframe_url', 2048)->nullable()->after('market_discount_tab_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'market_discount_iframe_url')) {
                $table->dropColumn('market_discount_iframe_url');
            }
            if (Schema::hasColumn('settings', 'market_discount_tab_title')) {
                $table->dropColumn('market_discount_tab_title');
            }
        });
    }
};