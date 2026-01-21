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
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->string('grade'); // A, B+, C, etc.
            $table->decimal('min_percentage', 5, 2); // 85.00
            $table->decimal('max_percentage', 5, 2); // 100.00
            $table->decimal('grade_point', 3, 2); // 4.00
            $table->text('description')->nullable();
            $table->boolean('is_passing')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('order');
            $table->index(['min_percentage', 'max_percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_scales');
    }
};
