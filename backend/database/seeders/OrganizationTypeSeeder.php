<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'National', 'level' => 1],
            ['name' => 'Area', 'level' => 2],
            ['name' => 'Regional', 'level' => 3],
            ['name' => 'District', 'level' => 4],
        ];

        foreach ($types as $type) {
            if (OrganizationType::where('name', $type['name'])->exists()) {
                continue;
            }

            // DatabaseSeeder uses WithoutModelEvents, so OrganizationType's
            // `creating` event (which normally auto-generates uuid) won't
            // fire here - set it explicitly. forceCreate() bypasses
            // mass-assignment protection since `uuid` is intentionally not
            // in Fillable.
            OrganizationType::forceCreate([
                'uuid' => (string) Str::uuid(),
                'name' => $type['name'],
                'level' => $type['level'],
                'is_active' => true,
            ]);
        }
    }
}
