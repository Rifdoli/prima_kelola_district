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
        Schema::table('roles', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('role_id');
            $table->string('description')->nullable()->after('sname');
            $table->boolean('is_active')->default(true)->after('description');

            $table->foreignId('created_by')->nullable()->after('is_active')
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_at')
                ->constrained('users', 'user_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['uuid', 'description', 'is_active']);
        });
    }
};
