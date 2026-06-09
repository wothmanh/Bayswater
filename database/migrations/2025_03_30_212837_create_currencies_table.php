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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('symbol', 5)->nullable();
            $table->decimal('sar_price', 10, 4)->nullable()->comment('Conversion rate to SAR');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Now, add the foreign key constraint to the schools table
        Schema::table('schools', function (Blueprint $table) {
            // Ensure the column exists and is unsigned before adding the constraint
            if (Schema::hasColumn('schools', 'currency_id')) {
                 $table->foreign('currency_id')
                       ->references('id')->on('currencies')
                       ->onDelete('set null'); // Or restrict, cascade, etc. depending on requirements
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint first
         Schema::table('schools', function (Blueprint $table) {
            // Check if the constraint exists before dropping
             try {
                 $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('schools');
                 $hasForeignKey = false;
                 foreach ($foreignKeys as $foreignKey) {
                     if ($foreignKey->getForeignTableName() === 'currencies' && $foreignKey->getLocalColumns() === ['currency_id']) {
                         $hasForeignKey = true;
                         break;
                     }
                 }
                 if ($hasForeignKey) {
                     $table->dropForeign(['currency_id']);
                 }
             } catch (\Exception $e) {
                 // Handle cases where the table or schema manager might not be available during rollback
                 \Log::warning("Could not drop foreign key for schools.currency_id: " . $e->getMessage());
             }
         });

        Schema::dropIfExists('currencies');
    }
};
