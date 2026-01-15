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
        Schema::table('final_exams', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('submitted_for_moderation_at')->nullable();
            $table->enum('moderation_status', ['draft', 'submitted', 'moderated', 'approved', 'published'])->default('draft');
            $table->foreignId('moderated_by')->nullable()->constrained('users');
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('moderation_notes')->nullable();
            
            $table->index('moderation_status');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_exams', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn([
                'locked_at',
                'submitted_for_moderation_at',
                'moderation_status',
                'moderated_by',
                'moderated_at',
                'published_at',
                'moderation_notes'
            ]);
        });
    }
};
