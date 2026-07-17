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
        Schema::create('question_groups', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->enum('type', ['domain', 'practice_area']);
            $table->string('name');
            $table->decimal('weight', 5, 2)->nullable();
            $table->unsignedSmallInteger('parent_id')->nullable();

            $table->index('parent_id');
            $table->foreign('parent_id')
                ->references('id')
                ->on('question_groups')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_groups');
    }
};
