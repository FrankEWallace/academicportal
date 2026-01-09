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
        Schema::create('grade_points', function (Blueprint $table) {
            $table->id();
            $table->string('letter_grade', 5)->unique(); // A+, A, A-, B+, etc.
            $table->decimal('min_percentage', 5, 2); // Minimum percentage for this grade
            $table->decimal('max_percentage', 5, 2); // Maximum percentage for this grade
            $table->decimal('grade_point', 3, 2); // GPA point value (e.g., 4.00, 3.67)
            $table->string('description')->nullable(); // Description like "Excellent", "Good"
            $table->boolean('is_passing')->default(true); // Whether this grade passes the course
            $table->integer('order')->default(0); // Display order
            $table->timestamps();

            // Indexes
            $table->index('letter_grade');
            $table->index('grade_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_points');
    }
};
