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
        Schema::create('continuous_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('semester_code', 20);
            $table->enum('assessment_type', ['quiz', 'assignment', 'midterm', 'project']); 
            $table->integer('assessment_number')->default(1); // Quiz 1, Quiz 2, etc.
            $table->decimal('score', 5, 2); // out of max_score
            $table->decimal('max_score', 5, 2)->default(10); // max points for this assessment
            $table->decimal('weight', 5, 2)->default(5); // weight towards 30 CA marks
            $table->decimal('weighted_score', 5, 2)->nullable(); // calculated: (score/max_score) * weight
            $table->date('assessment_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'course_id', 'semester_code']);
            $table->index('assessment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('continuous_assessments');
    }
};
