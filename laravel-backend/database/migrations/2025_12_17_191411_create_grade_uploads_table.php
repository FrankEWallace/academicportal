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
        Schema::create('grade_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->enum('grade_type', ['assignment', 'test', 'exam', 'quiz'])->default('assignment');
            $table->string('title'); // e.g., "Midterm Exam - Math 101"
            $table->string('file_path'); // Path to uploaded Excel file
            $table->string('original_filename');
            $table->json('upload_metadata')->nullable(); // File size, type, etc.
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('processing_results')->nullable(); // Success/error details
            $table->integer('total_records')->nullable();
            $table->integer('successful_records')->nullable();
            $table->integer('failed_records')->nullable();
            $table->text('error_messages')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['course_id', 'teacher_id']);
            $table->index('grade_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_uploads');
    }
};
