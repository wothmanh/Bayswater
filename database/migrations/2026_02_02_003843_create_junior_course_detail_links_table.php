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
        Schema::create('junior_course_detail_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('junior_course_id')->constrained('courses')->onDelete('cascade');
            $table->string('button_text');
            $table->text('url'); // Using text to allow long URLs
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('junior_course_detail_links');
    }
};
