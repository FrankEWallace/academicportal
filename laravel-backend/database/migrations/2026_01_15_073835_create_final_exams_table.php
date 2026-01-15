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
        Schema::create('final_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('semester_code', 20);
            $table->decimal('score', 5, 2); // out of 70
            $table->decimal('max_score', 5, 2)->default(70); // typically 70 points
            $table->date('exam_date')->nullable();
            $table->string('exam_venue')->nullable();
            $table->enum('status', ['pending', 'taken', 'absent', 'deferred'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'course_id', 'semester_code']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_exams');
    }
};
