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
        Schema::create('student_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // e.g., "FB-2025-0001"
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('category', ['academic', 'accommodation', 'fees', 'portal', 'general', 'complaint'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['submitted', 'in_review', 'in_progress', 'resolved', 'closed'])->default('submitted');
            $table->date('submission_date');
            $table->date('resolved_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('response_count')->default(0);
            $table->boolean('student_viewed_response')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('ticket_number');
            $table->index(['student_id', 'status']);
            $table->index('category');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_feedback');
    }
};
