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
        Schema::table('student_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('student_feedback', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users');
                $table->foreignId('assigned_by')->nullable()->constrained('users');
                $table->timestamp('assigned_at')->nullable();
                $table->string('department', 100)->nullable();
                $table->foreignId('priority_changed_by')->nullable()->constrained('users');
                $table->timestamp('priority_changed_at')->nullable();
                
                $table->index('assigned_to');
                $table->index('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_feedback', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['assigned_by']);
            $table->dropForeign(['priority_changed_by']);
            $table->dropColumn([
                'assigned_to',
                'assigned_by',
                'assigned_at',
                'department',
                'priority_changed_by',
                'priority_changed_at'
            ]);
        });
    }
};
