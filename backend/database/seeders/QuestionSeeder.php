<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionGroup;
use App\Models\QuestionCriteria;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $this->validateOldRecords();
        $path = database_path('seeders/data/self_assessment_master.json');
        $rows = json_decode(file_get_contents($path), true);
        // $timestamp = now();

        $questions = [];
        $questionGroups = [];
        $questionCriterias = [];
        $criteriaKeys = ['A', 'B', 'C', 'D', 'E'];
        $questionId = 0;
        $groupId = 0;
        $criteriaId = 0;

        foreach ($rows as $r => $row) {
            /** @var ?string */
            $domainName = $row['domain'] ?? null;
            if (!$domainName) {
                throw new \Exception("rows[$r]['domain'] is invalid");
            } elseif (!array_key_exists($domainName, $questionGroups)) {
                $groupId++;
                $questionGroups[$domainName] = [
                    'id' => $groupId,
                    'type' => 'domain',
                    'name' => $domainName,
                    'weight' => null,
                    'parent_id' => null,
                ];
            }

            /** @var ?string */
            $practiceAreaName = $row['practice_area'] ?? null;
            if (!$practiceAreaName) {
                throw new \Exception("rows[$r]['practice_area'] is invalid");
            } elseif (!array_key_exists($practiceAreaName, $questionGroups)) {
                $groupId++;
                $questionGroups[$practiceAreaName] = [
                    'id' => $groupId,
                    'type' => 'practice_area',
                    'name' => $practiceAreaName,
                    'weight' => null,
                    'parent_id' => $questionGroups[$domainName]['id'],
                ];
            }

            $questionId++;
            $questions[$questionId] = [
                'id' => $questionId,
                'practice_area_id' => $questionGroups[$practiceAreaName]['id'],
                'question' => $row['question'],
                'scope' => $row['scope'],
                'references' => $row['references'],
                'perangkat' => $row['perangkat'] == '-' ? null : $row['perangkat'],
                'max_score' => 0,
                'sort_order' => $row['row'],
                // 'created_at' => $timestamp,
                // 'updated_at' => $timestamp,
            ];

            foreach ($criteriaKeys as $k) {
                /** @var ?string */
                $criteriaTitle = $row['criteria'][$k] ?? null;
                if (!$criteriaTitle) {
                    throw new \Exception("rows[$r]['criteria']['$k'] is invalid");
                }

                $criteriaId++;
                $questionCriterias[] = [
                    'id' => $criteriaId,
                    'question_id' => $questionId,
                    'title' => $criteriaTitle,
                ];
                $questions[$questionId]['max_score']++;
            }
        }

        $questionGroups = array_values($questionGroups);
        $questions = array_values($questions);

        QuestionGroup::fillAndInsert($questionGroups);
        Question::fillAndInsert($questions);
        QuestionCriteria::fillAndInsert($questionCriterias);
    }

    private function validateOldRecords(): void
    {
        $notEmptyModels = [];
        if (Question::exists()) $notEmptyModels[] = 'Question';
        if (QuestionGroup::exists()) $notEmptyModels[] = 'QuestionGroup';
        if (QuestionCriteria::exists()) $notEmptyModels[] = 'QuestionCriteria';
        if (!empty($notEmptyModels)) {
            throw new \LogicException(
                'The target models should be cleanup before seeding:'.
                implode(',', $notEmptyModels)
            );
        }
    }
}
