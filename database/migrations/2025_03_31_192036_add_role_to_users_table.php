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
        Schema::table('users', function (Blueprint $table) {
            // Add the role column after the email column
            $table->string('role')->default('agent')->after('email')->comment('User role: admin or agent');
            // Add an index for faster role lookups
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex(['role']);
            // Then drop the column
            $table->dropColumn('role');
        });
    }
};
