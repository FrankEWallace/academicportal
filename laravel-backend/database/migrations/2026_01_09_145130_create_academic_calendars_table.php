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
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('event_type', [
                'semester_start',
                'semester_end',
                'exam_period',
                'registration_period',
                'holiday',
                'break',
                'orientation',
                'graduation',
                'other'
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('semester')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026" or "2026"
            $table->text('description')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_year', 'semester']);
            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
