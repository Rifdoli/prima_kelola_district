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
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id('assessment_question_id');
            $table->string('domain');
            $table->decimal('weight_domain', 5, 2)->nullable();
            $table->string('references')->nullable();
            $table->string('practice_area');
            $table->decimal('weight_practice_area', 5, 2)->nullable();
            $table->string('scope')->nullable();
            $table->string('perangkat')->nullable();
            $table->text('question');
            $table->text('criteria_a');
            $table->text('criteria_b');
            $table->text('criteria_c');
            $table->text('criteria_d');
            $table->text('criteria_e');
            $table->unsignedTinyInteger('max_score')->default(5);
            $table->unsignedInteger('sort_order')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
