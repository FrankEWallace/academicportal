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
        Schema::create('insurance_config', function (Blueprint $table) {
            $table->id();
            $table->enum('requirement_level', ['mandatory', 'optional', 'disabled'])->default('optional');
            $table->boolean('blocks_registration')->default(false);
            $table->string('academic_year', 10); // e.g., "2025/2026"
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
            
            $table->index('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_config');
    }
};
