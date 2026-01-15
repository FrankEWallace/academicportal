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
        Schema::table('continuous_assessments', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->index('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('continuous_assessments', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'locked_at', 
                'locked_by', 
                'submitted_for_approval_at', 
                'approval_status', 
                'approved_by', 
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
