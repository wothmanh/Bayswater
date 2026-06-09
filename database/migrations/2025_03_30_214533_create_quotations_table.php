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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null'); // Link to agent user
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->date('client_birthday')->nullable();
            $table->foreignId('client_nationality_country_id')->nullable()->constrained('countries')->onDelete('set null');

            // Core selections
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->date('course_start_date');
            $table->unsignedInteger('course_duration_weeks');
            $table->foreignId('accommodation_id')->nullable()->constrained('accommodations')->onDelete('set null');
            $table->unsignedInteger('accommodation_duration_weeks')->nullable();
            $table->json('selected_addons')->nullable()->comment('Stores IDs and quantities/details of selected addons'); // e.g., [{"id": 1, "type": "Airport Transfer", "quantity": 1}, {"id": 5, "type": "Insurance", "weeks": 12}]

            // Calculation results
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->json('calculated_result')->comment('Stores detailed cost breakdown from FeeCalculatorService');
            $table->decimal('total_price', 12, 2);

            // Status and metadata
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('agent_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
