<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang kolom evidence lama (tunggal per pertanyaan). Digantikan oleh
     * evidence_files (JSON per kriteria); evidence_note tidak dipakai lagi.
     */
    public function up(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->dropColumn(['evidence_note', 'evidence_file']);
        });
    }

    public function down(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->text('evidence_note')->nullable()->after('achieved_levels');
            $table->string('evidence_file')->nullable()->after('evidence_note');
        });
    }
};
