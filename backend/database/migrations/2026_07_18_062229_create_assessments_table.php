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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations', 'organization_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->char('period', length: 7); // contoh: "2026-Q3"
            $table->enum('type', ['SA', 'ODA', 'OSA']); // Self Assessment, On Desk Assessment, On Site Assessment

            $table->enum('status', ['open', 'draft', 'submitted'])->default('open');
            /**
             * Masih dimatikan, fitur rollback ODA->SA dan OSA->ODA belum dibutuhkan
             */
            // $table->enum('status', ['open', 'draft', 'submitted', 're-open'])->default('open');
            // $table->smallInteger('step');

            $table->boolean('is_active'); // Penanda assessment terakhir di grup quartalnya dan masih updatable
            $table->decimal('total_score', 6, 2)->nullable();
            $table->foreignId('prev_assessment_id')
                ->nullable()
                ->constrained('assessments', 'id')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('created_at');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->index(['organization_id', 'period', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
