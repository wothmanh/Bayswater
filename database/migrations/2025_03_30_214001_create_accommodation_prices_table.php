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
        Schema::create('accommodation_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->unsignedInteger('min_weeks')->comment('Start of duration range');
            $table->unsignedInteger('max_weeks')->comment('End of duration range');
            $table->decimal('price_per_week', 8, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Add index for faster lookups based on accommodation and duration
            $table->index(['accommodation_id', 'min_weeks', 'max_weeks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_prices');
    }
};
