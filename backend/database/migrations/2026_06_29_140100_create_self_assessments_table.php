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
        Schema::create('self_assessments', function (Blueprint $table) {
            $table->id('self_assessment_id');
            $table->foreignId('organization_id')
                ->constrained('organizations', 'organization_id')->cascadeOnDelete();
            $table->string('period'); // contoh: "2026-Q3"
            $table->enum('status', ['open', 'draft', 'submitted'])->default('open');
            $table->foreignId('submitted_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_assessments');
    }
};
