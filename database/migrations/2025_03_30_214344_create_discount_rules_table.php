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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Admin identifier for the rule');
            $table->text('description')->nullable();

            $table->enum('discount_type', ['percentage', 'fixed_amount', 'fee_waiver']);
            $table->decimal('discount_value', 10, 2)->nullable()->comment('Percentage or fixed amount');

            $table->enum('applies_to', [
                'course_tuition',
                'accommodation_price',
                'registration_fee', // Waives school registration fee
                'accommodation_fee', // Waives school accommodation placement fee
                'addon' // Applies to a specific addon price
            ]);
            $table->foreignId('addon_id')->nullable()->constrained('addons')->onDelete('cascade');

            // Conditions (nullable means condition doesn't apply)
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->foreignId('course_type_id')->nullable()->constrained('course_types')->onDelete('cascade');
            $table->foreignId('accommodation_id')->nullable()->constrained('accommodations')->onDelete('cascade');
            $table->string('accommodation_type')->nullable()->comment('e.g., Homestay, Residence');

            $table->unsignedInteger('min_course_weeks')->nullable();
            $table->unsignedInteger('max_course_weeks')->nullable();
            $table->unsignedInteger('min_accommodation_weeks')->nullable();
            $table->unsignedInteger('max_accommodation_weeks')->nullable();

            $table->date('valid_from_date')->nullable();
            $table->date('valid_to_date')->nullable();
            $table->enum('date_condition_type', ['booking_date', 'start_date'])->nullable();

            // Application rules
            $table->boolean('combinable')->default(false)->comment('Can be combined with other discounts');
            $table->integer('priority')->default(0)->comment('Order of application if not combinable');

            $table->boolean('active')->default(true);
            $table->timestamps();

            // Add indexes for common query conditions
            $table->index(['active', 'valid_from_date', 'valid_to_date']);
            $table->index(['school_id', 'active']);
            $table->index(['course_id', 'active']);
            $table->index(['accommodation_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
