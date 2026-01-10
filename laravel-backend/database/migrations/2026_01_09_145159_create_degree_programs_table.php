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
        Schema::create('degree_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Bachelor of Science in Computer Science"
            $table->string('code')->unique(); // e.g., "BSCS"
            $table->enum('degree_type', ['associate', 'bachelor', 'master', 'doctorate', 'certificate', 'diploma']);
            $table->integer('total_credits_required');
            $table->integer('duration_years');
            $table->integer('duration_semesters');
            $table->decimal('minimum_cgpa', 3, 2)->default(2.00); // Minimum CGPA to graduate
            $table->text('description')->nullable();
            $table->json('program_objectives')->nullable(); // Learning outcomes
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();
            
            $table->index('department_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_programs');
    }
};
