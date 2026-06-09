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
        Schema::create('airport_school', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            // Add any additional pivot fields if needed in the future
            // $table->timestamps(); // Usually not needed for simple pivot tables

            // Add unique constraint to prevent duplicate entries
            $table->unique(['airport_id', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airport_school');
    }
};
