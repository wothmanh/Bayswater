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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->string('name');
            // $table->foreignId('currency_id')->constrained('currencies'); // Add later after creating currencies table
            $table->unsignedBigInteger('currency_id')->nullable(); // Temporary placeholder

            $table->decimal('registration_fee', 8, 2)->default(0);
            $table->decimal('accommodation_fee', 8, 2)->nullable();
            $table->decimal('bank_charges', 8, 2)->nullable();
            $table->decimal('books_fee', 8, 2)->nullable();
            $table->unsignedInteger('books_weeks')->nullable()->comment('Apply book fee every X weeks, null if one-time');
            $table->decimal('insurance_fee_per_week', 8, 2)->nullable();
            $table->decimal('courier_fee', 8, 2)->nullable();
            $table->decimal('guardianship_fee_per_week', 8, 2)->nullable();
            $table->decimal('custodianship_fee', 8, 2)->nullable()->comment('One-time fee');
            $table->decimal('christmas_fee_per_week', 8, 2)->nullable();
            $table->date('christmas_start_date')->nullable();
            $table->date('christmas_end_date')->nullable();
            $table->decimal('summer_fee_per_week', 8, 2)->nullable();
            $table->date('summer_start_date')->nullable();
            $table->date('summer_end_date')->nullable();
            $table->unsignedInteger('summer_fee_weeks_off')->nullable()->comment('Waive summer fee if course duration >= X weeks');
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
        Schema::dropIfExists('schools');
    }
};
