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
        Schema::create('feedback_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('student_feedback')->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_type'); // e.g., "image/png", "application/pdf"
            $table->integer('file_size'); // in bytes
            $table->date('uploaded_date');
            $table->timestamps();
            
            // Indexes
            $table->index('feedback_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_attachments');
    }
};
