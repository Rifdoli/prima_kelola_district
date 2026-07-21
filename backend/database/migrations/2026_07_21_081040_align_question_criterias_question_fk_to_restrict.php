<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * File migration create_question_criterias_table sempat diedit setelah dijalankan
 * (cascadeOnDelete -> restrictOnDelete), jadi DB yang sudah terlanjur migrate
 * memakai CASCADE sementara DB baru memakai RESTRICT. Migration ini menyamakan
 * keduanya ke RESTRICT: kriteria tidak boleh ikut terhapus diam-diam karena
 * jawaban district merujuk ke question_criterias.id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->resetForeign(fn (Blueprint $table) => $table->foreign('question_id')
            ->references('id')->on('questions')
            ->cascadeOnUpdate()
            ->restrictOnDelete());
    }

    public function down(): void
    {
        $this->resetForeign(fn (Blueprint $table) => $table->foreign('question_id')
            ->references('id')->on('questions')
            ->cascadeOnUpdate()
            ->cascadeOnDelete());
    }

    private function resetForeign(callable $addForeign): void
    {
        // SQLite tidak mendukung drop foreign key; di sana FK sudah RESTRICT sejak awal
        if (DB::connection()->getDriverName() === 'sqlite') return;

        Schema::table('question_criterias', function (Blueprint $table) use ($addForeign) {
            $table->dropForeign(['question_id']);
            $addForeign($table);
        });
    }
};
