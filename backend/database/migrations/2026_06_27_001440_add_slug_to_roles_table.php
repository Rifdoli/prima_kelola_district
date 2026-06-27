<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('sname', 50)->nullable()->unique()->after('name');
        });

        // Backfill: sname jadi versi stabil dari name pada saat ini, supaya
        // role lama tidak kehilangan identitas walau name sempat diubah.
        DB::table('roles')->whereNull('sname')->update(['sname' => DB::raw('lower(name)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('sname');
        });
    }
};
