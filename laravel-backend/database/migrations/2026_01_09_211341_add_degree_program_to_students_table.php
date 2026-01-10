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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('degree_program_id')->nullable()->after('department_id')->constrained()->onDelete('set null');
            $table->integer('total_credits_earned')->default(0)->after('cgpa');
            $table->integer('current_semester')->default(1)->after('total_credits_earned');
            $table->date('expected_graduation_date')->nullable()->after('current_semester');
            $table->enum('academic_status', [
                'active',
                'probation',
                'suspended',
                'graduated',
                'withdrawn',
                'on_leave'
            ])->default('active')->after('expected_graduation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['degree_program_id']);
            $table->dropColumn([
                'degree_program_id',
                'total_credits_earned',
                'current_semester',
                'expected_graduation_date',
                'academic_status'
            ]);
        });
    }
};
