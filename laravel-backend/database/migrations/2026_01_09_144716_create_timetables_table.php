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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->string('room_number')->nullable();
            $table->string('building')->nullable();
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('semester');
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->string('section')->nullable(); // For multiple sections of same course
            $table->integer('capacity')->default(50);
            $table->integer('enrolled_count')->default(0);
            $table->enum('status', ['active', 'cancelled', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['course_id', 'semester', 'academic_year']);
            $table->index(['teacher_id', 'day_of_week']);
            $table->index(['day_of_week', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
