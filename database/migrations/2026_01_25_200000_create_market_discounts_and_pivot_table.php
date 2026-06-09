<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('iframe_url')->nullable();
            $table->timestamps();
        });

        Schema::create('market_discount_region', function (Blueprint $table) {
            $table->foreignId('market_discount_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->primary(['market_discount_id', 'region_id']);
            $table->timestamps();
        });

        // Migrate existing data from settings
        $setting = DB::table('settings')->first();
        if ($setting) {
            if (!empty($setting->market_discount_tab_title) || !empty($setting->market_discount_iframe_url)) {
                DB::table('market_discounts')->insert([
                    'title' => $setting->market_discount_tab_title,
                    'iframe_url' => $setting->market_discount_iframe_url,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_discount_region');
        Schema::dropIfExists('market_discounts');
    }
};
