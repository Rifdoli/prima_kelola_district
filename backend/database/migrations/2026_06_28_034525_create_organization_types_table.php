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
        Schema::create('organization_types', function (Blueprint $table) {
            $table->id('organization_type_id');
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('level');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_types');
    }
};
