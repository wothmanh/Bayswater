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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('course_type_id')->constrained('course_types')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('lessons_per_week')->nullable();
            $table->decimal('hours_per_week', 5, 2)->nullable();
            $table->string('study_mode')->nullable(); // e.g., 'Full-time', 'Part-time'
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('pricing_type', ['per_week', 'fixed_schedule'])->default('per_week');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
