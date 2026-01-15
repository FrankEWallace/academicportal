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
        Schema::create('feedback_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('student_feedback')->onDelete('cascade');
            $table->foreignId('responder_id')->constrained('users')->onDelete('cascade'); // admin/staff
            $table->text('message');
            $table->date('response_date');
            $table->boolean('is_internal_note')->default(false); // visible only to staff
            $table->timestamps();
            
            // Indexes
            $table->index('feedback_id');
            $table->index('responder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_responses');
    }
};
