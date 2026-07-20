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
        Schema::create('assessment_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_answer_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('question_criteria_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->boolean('value');
            $table->string('evidence_path')->nullable();
            $table->string('note')->nullable();

            // index untuk optimasi pengecekan cleanup evidence
            $table->index('evidence_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_criterias');
    }
};
