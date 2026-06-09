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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable()->comment('e.g., Homestay, Residence');
            $table->string('room_type')->nullable()->comment('e.g., Single, Twin');
            $table->string('meal_plan')->nullable()->comment('e.g., Half Board, Self-catering');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->boolean('requires_guardianship')->default(false)->comment('Apply guardianship fee if student under 18');
            $table->boolean('requires_christmas_supplement')->default(false)->comment('Apply Christmas fee during defined period');

            $table->decimal('summer_fee_per_week', 8, 2)->nullable();
            $table->date('summer_start_date')->nullable();
            $table->date('summer_end_date')->nullable();
            $table->text('summer_fee_note')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
