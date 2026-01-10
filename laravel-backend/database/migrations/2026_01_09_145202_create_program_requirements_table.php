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
        Schema::create('program_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('degree_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('requirement_type', [
                'core',           // Required core courses
                'major',          // Major-specific courses
                'minor',          // Minor requirements
                'elective',       // Elective courses
                'general_education', // Gen ed requirements
                'capstone'        // Final project/thesis
            ]);
            $table->integer('semester_recommended')->nullable(); // Which semester to take it
            $table->boolean('is_required')->default(true);
            $table->integer('credits');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Prevent duplicate requirements
            $table->unique(['degree_program_id', 'course_id']);
            $table->index(['degree_program_id', 'requirement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_requirements');
    }
};
