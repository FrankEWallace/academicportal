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
        Schema::create('accommodation_roommates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('student_accommodations')->onDelete('cascade');
            $table->foreignId('roommate_student_id')->constrained('students')->onDelete('cascade');
            $table->string('roommate_name');
            $table->string('roommate_matric_no');
            $table->string('roommate_department')->nullable();
            $table->string('roommate_level')->nullable();
            $table->string('roommate_phone')->nullable();
            $table->string('roommate_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('accommodation_id');
            $table->index('roommate_student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_roommates');
    }
};
