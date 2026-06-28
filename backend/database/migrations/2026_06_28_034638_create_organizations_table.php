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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('organization_id');
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('sname', 50)->unique();
            $table->foreignId('organization_type_id')
                ->constrained('organization_types', 'organization_type_id')->restrictOnDelete();
            $table->foreignId('parent_organization_id')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('parent_organization_id')
                ->references('organization_id')->on('organizations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
