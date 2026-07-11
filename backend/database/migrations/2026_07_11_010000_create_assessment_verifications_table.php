<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Verifikasi berjenjang atas Self Assessment district.
     * - on_desk (ODA): oleh Regional, memverifikasi klaim Self district.
     * - on_site (OSA): oleh Area/Nasional, memverifikasi hasil ODA
     *   (parent_verification_id menunjuk ke baris ODA).
     */
    public function up(): void
    {
        Schema::create('assessment_verifications', function (Blueprint $table) {
            $table->id('assessment_verification_id');
            $table->foreignId('self_assessment_id')
                ->constrained('self_assessments', 'self_assessment_id')->cascadeOnDelete();
            $table->foreignId('parent_verification_id')->nullable()
                ->constrained('assessment_verifications', 'assessment_verification_id')->nullOnDelete();
            $table->enum('type', ['on_desk', 'on_site']);
            $table->enum('status', ['open', 'draft', 'submitted'])->default('open');
            $table->foreignId('verified_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->unique(['self_assessment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_verifications');
    }
};
