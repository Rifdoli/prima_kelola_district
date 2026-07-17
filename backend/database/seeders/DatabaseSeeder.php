<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(OrganizationTypeSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(PilotingUserSeeder::class);
        $this->call(QuestionSeeder::class);

        /** @todo Akan dihapus nanti saat fitur sudah di-refactor */
        $this->call(AssessmentQuestionSeeder::class);
    }
}
