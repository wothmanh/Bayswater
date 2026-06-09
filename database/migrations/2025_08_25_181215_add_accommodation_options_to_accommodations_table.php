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
        Schema::table('accommodations', function (Blueprint $table) {
            $table->boolean('private_bathroom_enabled')->default(false)->after('active');
            $table->decimal('private_bathroom_fee', 8, 2)->nullable()->after('private_bathroom_enabled');
            $table->boolean('dietary_supplement_enabled')->default(false)->after('private_bathroom_fee');
            $table->decimal('dietary_supplement_fee', 8, 2)->nullable()->after('dietary_supplement_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'private_bathroom_enabled',
                'private_bathroom_fee',
                'dietary_supplement_enabled',
                'dietary_supplement_fee'
            ]);
        });
    }
};
