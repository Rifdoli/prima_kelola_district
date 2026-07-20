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
        Schema::table('question_criterias', function (Blueprint $table) {
            $table->string('code', 8)->nullable()->after('question_id');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('code');
            $table->text('reference')->nullable()->after('title');
            $table->text('evidence_hint')->nullable()->after('reference');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('references');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('references')->nullable()->after('scope');
        });

        Schema::table('question_criterias', function (Blueprint $table) {
            $table->dropColumn(['code', 'sort_order', 'reference', 'evidence_hint']);
        });
    }
};
