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
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->json('notes')->nullable()->after('evidence_files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
