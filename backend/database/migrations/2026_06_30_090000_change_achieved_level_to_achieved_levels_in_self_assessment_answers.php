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
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->json('achieved_levels')->nullable()->after('assessment_question_id');
        });

        // Migrasi data lama: satu level tunggal -> array berisi level tsb.
        DB::table('self_assessment_answers')
            ->whereNotNull('achieved_level')
            ->orderBy('self_assessment_answer_id')
            ->each(function ($row) {
                DB::table('self_assessment_answers')
                    ->where('self_assessment_answer_id', $row->self_assessment_answer_id)
                    ->update(['achieved_levels' => json_encode([$row->achieved_level])]);
            });

        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->dropColumn('achieved_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->enum('achieved_level', ['A', 'B', 'C', 'D', 'E'])->nullable()->after('assessment_question_id');
        });

        // Ambil level tertinggi (huruf terbesar) dari array sebagai representasi tunggal saat rollback.
        DB::table('self_assessment_answers')
            ->whereNotNull('achieved_levels')
            ->orderBy('self_assessment_answer_id')
            ->each(function ($row) {
                $levels = json_decode($row->achieved_levels, true) ?: [];
                sort($levels);
                $highest = $levels ? end($levels) : null;

                DB::table('self_assessment_answers')
                    ->where('self_assessment_answer_id', $row->self_assessment_answer_id)
                    ->update(['achieved_level' => $highest]);
            });

        Schema::table('self_assessment_answers', function (Blueprint $table) {
            $table->dropColumn('achieved_levels');
        });
    }
};
