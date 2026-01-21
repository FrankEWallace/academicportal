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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Semester 1", "Fall 2025"
            $table->integer('semester_number'); // 1, 2, 3 (for trimester systems)
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->date('registration_start_date')->nullable();
            $table->date('registration_end_date')->nullable();
            $table->date('add_drop_deadline')->nullable();
            $table->date('exam_start_date')->nullable();
            $table->date('exam_end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
            $table->index(['academic_year_id', 'semester_number']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
