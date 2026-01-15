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
        Schema::table('student_accommodations', function (Blueprint $table) {
            $table->foreignId('allocated_by')->nullable()->constrained('users');
            $table->timestamp('allocated_at')->nullable();
            $table->foreignId('vacated_by')->nullable()->constrained('users');
            $table->timestamp('vacated_at')->nullable();
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            
            $table->index('room_id');
            $table->index('allocated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_accommodations', function (Blueprint $table) {
            $table->dropForeign(['allocated_by']);
            $table->dropForeign(['vacated_by']);
            $table->dropForeign(['room_id']);
            $table->dropColumn([
                'allocated_by',
                'allocated_at',
                'vacated_by',
                'vacated_at',
                'room_id'
            ]);
        });
    }
};
