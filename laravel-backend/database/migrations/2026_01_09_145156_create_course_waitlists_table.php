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
        Schema::create('course_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('position'); // Position in waitlist queue
            $table->integer('semester');
            $table->year('academic_year');
            $table->enum('status', ['waiting', 'enrolled', 'removed', 'expired'])->default('waiting');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate waitlist entries
            $table->unique(['course_id', 'student_id', 'semester', 'academic_year']);
            $table->index(['course_id', 'status', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_waitlists');
    }
};
