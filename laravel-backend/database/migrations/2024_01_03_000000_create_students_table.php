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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->date('admission_date');
            $table->foreignId('department_id')->constrained();
            $table->integer('semester')->default(1);
            $table->string('section')->nullable();
            $table->string('batch')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->decimal('current_gpa', 3, 2)->nullable();
            $table->integer('total_credits')->default(0);
            $table->date('graduation_date')->nullable();
            $table->enum('status', ['enrolled', 'graduated', 'dropped', 'suspended'])->default('enrolled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
