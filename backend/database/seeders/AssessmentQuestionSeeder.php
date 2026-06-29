<?php

namespace Database\Seeders;

use App\Models\AssessmentQuestion;
use Illuminate\Database\Seeder;

class AssessmentQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/self_assessment_master.json');
        $rows = json_decode(file_get_contents($path), true);

        foreach ($rows as $row) {
            AssessmentQuestion::updateOrCreate(
                ['sort_order' => $row['row']],
                [
                    'domain' => $row['domain'],
                    'weight_domain' => $row['weight_domain'],
                    'references' => $row['references'],
                    'practice_area' => $row['practice_area'],
                    'weight_practice_area' => $row['weight_practice_area'],
                    'scope' => $row['scope'],
                    'perangkat' => $row['perangkat'],
                    'question' => $row['question'],
                    'criteria_a' => $row['criteria']['A'],
                    'criteria_b' => $row['criteria']['B'],
                    'criteria_c' => $row['criteria']['C'],
                    'criteria_d' => $row['criteria']['D'],
                    'criteria_e' => $row['criteria']['E'],
                    'max_score' => $row['max_score'],
                ]
            );
        }
    }
}
