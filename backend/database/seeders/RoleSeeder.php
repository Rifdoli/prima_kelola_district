<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'SUPERADMIN', 'sname' => 'admin_sup', 'description' => 'User Pengelola Utama Aplikasi'],
            ['name' => 'ADMIN NASIONAL', 'sname' => 'admin_nas', 'description' => 'User Utama Nasional'],
            ['name' => 'ADMIN AREA', 'sname' => 'admin_are', 'description' => 'User Utama Area'],
            ['name' => 'ADMIN REGIONAL', 'sname' => 'admin_reg', 'description' => 'User Utama Regional'],
            ['name' => 'ADMIN DISTRICT', 'sname' => 'admin_dis', 'description' => 'User Utama District'],
        ];

        foreach ($roles as $role) {
            if (Role::where('sname', $role['sname'])->exists()) {
                continue;
            }

            // DatabaseSeeder uses WithoutModelEvents, so Role's `creating`
            // event (which normally auto-generates uuid) won't fire here -
            // set it explicitly. forceCreate() bypasses mass-assignment
            // protection since `uuid` is intentionally not in Fillable.
            Role::forceCreate([
                'uuid' => (string) Str::uuid(),
                'name' => $role['name'],
                'sname' => $role['sname'],
                'description' => $role['description'],
                'is_active' => true,
            ]);
        }
    }
}
