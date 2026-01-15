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
        Schema::create('enrollment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->enum('action', [
                'created', 
                'approved', 
                'rejected', 
                'removed', 
                'confirmed'
            ]);
            $table->foreignId('performed_by')->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('enrollment_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_audit_logs');
    }
};
