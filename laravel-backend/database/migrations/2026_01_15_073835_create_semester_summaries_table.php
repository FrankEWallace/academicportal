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
        Schema::create('semester_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('semester_code', 20); // e.g., "2024-1"
            $table->string('academic_year', 10); // e.g., "2024/2025"
            $table->integer('total_courses')->default(0);
            $table->integer('total_units')->default(0);
            $table->decimal('semester_gpa', 3, 2)->default(0); // e.g., 3.45
            $table->decimal('cumulative_gpa', 3, 2)->default(0); // e.g., 3.21
            $table->integer('total_units_earned')->default(0); // cumulative
            $table->enum('semester_status', ['in_progress', 'completed', 'probation', 'suspended'])->default('in_progress');
            $table->string('transcript_path')->nullable(); // PDF download
            $table->date('transcript_generated_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'semester_code']);
            $table->index('semester_status');
            $table->unique(['student_id', 'semester_code']); // one summary per student per semester
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester_summaries');
    }
};
