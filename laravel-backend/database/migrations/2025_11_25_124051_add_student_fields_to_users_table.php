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
        Schema::table('users', function (Blueprint $table) {
            $table->string('program')->nullable()->after('role'); // Academic program (e.g., "Bachelor of Computer Science")
            $table->string('year_level')->nullable()->after('program'); // Academic year (1st, 2nd, 3rd, 4th year)
            $table->string('student_status')->default('active')->after('year_level'); // active, inactive, graduated, suspended
            $table->date('enrollment_date')->nullable()->after('student_status'); // Date when student enrolled
            $table->decimal('current_cgpa', 3, 2)->nullable()->after('enrollment_date'); // Current CGPA
            $table->string('bio')->nullable()->after('current_cgpa'); // Short biography
            $table->json('social_links')->nullable()->after('bio'); // Social media links
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'program',
                'year_level', 
                'student_status',
                'enrollment_date',
                'current_cgpa',
                'bio',
                'social_links'
            ]);
        });
    }
};
