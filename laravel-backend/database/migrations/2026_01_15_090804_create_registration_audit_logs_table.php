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
        Schema::create('registration_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->enum('action', [
                'created', 
                'fees_verified', 
                'insurance_verified', 
                'blocked', 
                'unblocked', 
                'overridden'
            ]);
            $table->foreignId('performed_by')->constrained('users');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('registration_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_audit_logs');
    }
};
