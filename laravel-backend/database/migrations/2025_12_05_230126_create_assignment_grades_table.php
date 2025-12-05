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
        Schema::create('assignment_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 5, 2); // Score obtained (e.g., 85.50)
            $table->text('feedback')->nullable(); // Teacher's feedback
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade'); // Who graded it
            $table->timestamp('graded_at')->nullable(); // When it was graded
            $table->timestamps();

            // Ensure a student can only have one grade per assignment
            $table->unique(['student_id', 'assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_grades');
    }
};
