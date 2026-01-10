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
        Schema::table('courses', function (Blueprint $table) {
            $table->json('grade_components')->nullable()->after('credits'); 
            // Store: {"assignments": 30, "midterm": 30, "final": 40}
            $table->boolean('has_curve')->default(false)->after('grade_components');
            $table->decimal('curve_percentage', 5, 2)->nullable()->after('has_curve');
            $table->integer('max_capacity')->default(50)->after('curve_percentage');
            $table->integer('min_capacity')->default(10)->after('max_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'grade_components',
                'has_curve',
                'curve_percentage',
                'max_capacity',
                'min_capacity'
            ]);
        });
    }
};
