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
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('school_id')->constrained()->onDelete('cascade'); // Link to schools table
            $table->decimal('arrival_price', 8, 2)->nullable(); // Price for arrival transfer
            $table->decimal('departure_price', 8, 2)->nullable(); // Price for departure transfer
            $table->boolean('active')->default(true);
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null'); // Optional link to cities
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null'); // Optional link to countries
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
