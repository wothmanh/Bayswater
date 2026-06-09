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
        Schema::create('airport_course_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['airport_id', 'course_type_id'], 'air_ctype_unique');
        });

        Schema::create('airport_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['airport_id', 'course_id'], 'air_course_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airport_course');
        Schema::dropIfExists('airport_course_type');
    }
};
