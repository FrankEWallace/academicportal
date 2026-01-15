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
        Schema::create('enrollment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('semester_code', 20); // e.g., "2024-1"
            $table->string('academic_year', 10); // e.g., "2024/2025"
            $table->integer('total_courses')->default(0);
            $table->integer('total_units')->default(0);
            $table->boolean('prerequisites_satisfied')->default(false);
            $table->boolean('schedule_conflicts_resolved')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->boolean('timetable_understood')->default(false);
            $table->boolean('attendance_policy_agreed')->default(false);
            $table->boolean('academic_calendar_checked')->default(false);
            $table->date('confirmation_date')->nullable();
            $table->string('confirmation_email_sent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'semester_code']);
            $table->index('confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_confirmations');
    }
};
