<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evidence file per kriteria (level A-E): map { "A": "path", ... }.
     * Menggantikan evidence_file tunggal per pertanyaan.
     */
    public function up(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->json('evidence_files')->nullable()->after('evidence_note');
        });
    }

    public function down(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->dropColumn('evidence_files');
        });
    }
};
