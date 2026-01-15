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
        Schema::create('student_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('academic_year', 10); // e.g., "2024/2025"
            $table->string('hostel_name');
            $table->string('block')->nullable();
            $table->string('floor')->nullable();
            $table->string('room_number');
            $table->enum('room_type', ['single', 'double', 'quad'])->default('quad');
            $table->integer('bed_number')->nullable();
            $table->enum('status', ['allocated', 'pending', 'expired', 'cancelled'])->default('allocated');
            $table->date('allocation_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('renewal_eligible')->default(false);
            $table->date('renewal_deadline')->nullable();
            $table->string('allocation_letter_path')->nullable(); // PDF
            $table->text('special_requirements')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'academic_year']);
            $table->index('status');
            $table->index(['hostel_name', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_accommodations');
    }
};
