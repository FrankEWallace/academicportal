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
        Schema::table('student_insurance', function (Blueprint $table) {
            if (!Schema::hasColumn('student_insurance', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('resubmission_requested_at')->nullable();
                
                $table->index('verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_insurance', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'verified_by',
                'verified_at',
                'rejection_reason',
                'resubmission_requested_at'
            ]);
        });
    }
};
