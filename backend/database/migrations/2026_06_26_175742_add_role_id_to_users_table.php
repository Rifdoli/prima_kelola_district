<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The `role_id` column itself now lives in create_users_table (for
     * correct physical column order) - this migration only adds the FK
     * constraint, since it must run after the `roles` table exists.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('role_id')->on('roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Only the FK constraint is dropped here - the `role_id` column itself
     * belongs to create_users_table (see up()), so a full rollback removes
     * it via dropIfExists('users') there, not here.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
    }
};
