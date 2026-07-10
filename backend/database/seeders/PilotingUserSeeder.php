<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PilotingUserSeeder extends Seeder
{
    /**
     * [username, organization sname, role sname].
     * organization sname null = tidak menempel ke organisasi (superadmin).
     */
    private const USERS = [
        ['superadmin', null, 'admin_sup'],
        ['admin_nasional', 'tif', 'admin_nas'],

        // District lokasi piloting
        ['admin_district_binjai', 'binj', 'admin_dis'],
        ['admin_district_karawang', 'krwa', 'admin_dis'],
        ['admin_district_flores', 'flrs', 'admin_dis'],
        ['admin_district_makassar', 'mksr', 'admin_dis'],

        // Regional (induk tiap district piloting)
        ['admin_regional_sumbagut', 'smut', 'admin_reg'],
        ['admin_regional_eastern_jabotabek', 'esja', 'admin_reg'],
        ['admin_regional_bali_nusra', 'banu', 'admin_reg'],
        ['admin_regional_sulawesi', 'sula', 'admin_reg'],

        // Area (kakek tiap district piloting)
        ['admin_area_1', 'tif1', 'admin_are'],
        ['admin_area_2', 'tif2', 'admin_are'],
        ['admin_area_3', 'tif3', 'admin_are'],
        ['admin_area_4', 'tif4', 'admin_are'],
    ];

    private const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $roleIdBySname = Role::pluck('role_id', 'sname');
        $orgIdBySname = Organization::pluck('organization_id', 'sname');

        foreach (self::USERS as [$username, $orgSname, $roleSname]) {
            $roleId = $roleIdBySname[$roleSname] ?? null;
            $orgId = $orgSname ? ($orgIdBySname[$orgSname] ?? null) : null;

            // Kalau role tidak ketemu, atau org diminta tapi tidak ketemu -> skip + warning.
            if ($roleId === null || ($orgSname !== null && $orgId === null)) {
                $this->command->warn("Skip {$username}: role/organisasi tidak ditemukan.");
                continue;
            }

            User::factory()->create([
                'username' => $username,
                'name' => $username,
                'email' => $username . '@primakelola.test',
                'organization_id' => $orgId,
                'role_id' => $roleId,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'is_active' => true,
                'is_ldap' => false,
            ]);
        }
    }
}
