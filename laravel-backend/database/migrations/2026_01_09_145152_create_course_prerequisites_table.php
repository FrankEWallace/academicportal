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
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // The course that has prerequisites
            $table->foreignId('prerequisite_course_id')->constrained('courses')->onDelete('cascade'); // The required prerequisite course
            $table->decimal('minimum_grade', 3, 2)->nullable(); // Minimum grade required in prerequisite (e.g., 2.00 for C)
            $table->enum('requirement_type', ['required', 'recommended', 'corequisite'])->default('required');
            // required: must complete before enrollment
            // recommended: suggested but not mandatory
            // corequisite: must take concurrently or have completed
            $table->timestamps();
            
            // Prevent duplicate prerequisites
            $table->unique(['course_id', 'prerequisite_course_id']);
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_prerequisites');
    }
};
