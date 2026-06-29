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
        Schema::create('self_assessment_answers', function (Blueprint $table) {
            $table->id('self_assessment_answer_id');
            $table->foreignId('self_assessment_id')
                ->constrained('self_assessments', 'self_assessment_id')->cascadeOnDelete();
            $table->foreignId('assessment_question_id')
                ->constrained('assessment_questions', 'assessment_question_id')->cascadeOnDelete();
            $table->enum('achieved_level', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->text('evidence_note')->nullable();
            $table->string('evidence_file')->nullable(); // path di disk 'public'
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->unique(['self_assessment_id', 'assessment_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_assessment_answers');
    }
};
