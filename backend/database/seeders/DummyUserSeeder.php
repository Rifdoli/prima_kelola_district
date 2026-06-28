<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * Pemetaan level tipe organisasi -> sname role.
     * Sesuai RoleSeeder & OrganizationTypeSeeder.
     */
    private const LEVEL_TO_ROLE_SNAME = [
        1 => 'admin_nas',
        2 => 'admin_are',
        3 => 'admin_reg',
        4 => 'admin_dis',
    ];

    public function run(): void
    {
        // sname role -> role_id
        $roleIdBySname = Role::pluck('role_id', 'sname');

        // level -> role_id
        $roleIdByLevel = [];
        foreach (self::LEVEL_TO_ROLE_SNAME as $level => $sname) {
            $roleIdByLevel[$level] = $roleIdBySname[$sname] ?? null;
        }

        $organizations = Organization::with('type')->get();

        foreach ($organizations as $org) {
            $level = $org->type?->level;
            if ($level === null || empty($roleIdByLevel[$level])) {
                continue;
            }

            // 2 user, keduanya pakai role sesuai tipe organisasi itu sendiri.
            User::factory()->count(2)->create([
                'organization_id' => $org->organization_id,
                'role_id' => $roleIdByLevel[$level],
            ]);
        }
    }
}
