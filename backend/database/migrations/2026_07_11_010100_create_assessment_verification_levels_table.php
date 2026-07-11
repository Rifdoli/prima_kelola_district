<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hasil verifikasi per kriteria (level A-E) per pertanyaan.
     * is_valid = verifikator membenarkan level yang diklaim.
     */
    public function up(): void
    {
        Schema::create('assessment_verification_levels', function (Blueprint $table) {
            $table->id('assessment_verification_level_id');
            $table->foreignId('assessment_verification_id')
                ->constrained('assessment_verifications', 'assessment_verification_id')->cascadeOnDelete();
            $table->foreignId('assessment_question_id')
                ->constrained('assessment_questions', 'assessment_question_id')->cascadeOnDelete();
            $table->enum('level', ['A', 'B', 'C', 'D', 'E']);
            $table->boolean('is_valid')->default(false);
            $table->text('note')->nullable();
            $table->string('evidence_file')->nullable(); // path di disk 'public'
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->unique(['assessment_verification_id', 'assessment_question_id', 'level'], 'verif_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_verification_levels');
    }
};
