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
        Schema::create('organization_mapping', function (Blueprint $table) {
            $table->id('organization_mapping_id');
            $table->foreignId('ancestor_id')
                ->constrained('organizations', 'organization_id')->cascadeOnDelete();
            $table->foreignId('descendant_id')
                ->constrained('organizations', 'organization_id')->cascadeOnDelete();
            $table->unsignedInteger('depth');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();

            // ancestor_id/descendant_id are already indexed individually via
            // their FK constraints above; this adds the composite uniqueness.
            $table->unique(['ancestor_id', 'descendant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_mapping');
    }
};
