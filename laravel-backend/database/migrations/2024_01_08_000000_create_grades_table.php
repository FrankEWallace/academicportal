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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->enum('assessment_type', ['assignment', 'quiz', 'midterm', 'final', 'project', 'presentation']);
            $table->string('assessment_name');
            $table->decimal('max_marks', 6, 2);
            $table->decimal('obtained_marks', 6, 2);
            $table->string('grade_letter')->nullable();
            $table->decimal('grade_point', 3, 2)->nullable();
            $table->date('assessment_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
