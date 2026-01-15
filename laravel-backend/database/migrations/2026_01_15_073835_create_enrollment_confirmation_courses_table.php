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
        Schema::create('enrollment_confirmation_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_confirmation_id');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('course_code', 20);
            $table->string('course_title');
            $table->integer('units');
            $table->boolean('prerequisites_met')->default(true);
            $table->boolean('has_schedule_conflict')->default(false);
            $table->text('conflict_details')->nullable();
            $table->timestamps();
            
            // Indexes and foreign keys
            $table->foreign('enrollment_confirmation_id', 'ec_courses_ec_id_fk')
                  ->references('id')
                  ->on('enrollment_confirmations')
                  ->onDelete('cascade');
            $table->index('enrollment_confirmation_id', 'ec_courses_ec_id_idx');
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_confirmation_courses');
    }
};
