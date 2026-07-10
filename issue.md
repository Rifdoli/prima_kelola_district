# Seeder User untuk Lokasi Piloting

## Tujuan

Ganti data user dummy yang ada sekarang dengan **user piloting yang terarah**: 4 district
lokasi piloting + user untuk regional & area di atas masing-masing district. Username dibuat
gampang diingat (`admin_district_makassar`, `admin_regional_sulawesi`, `admin_area_4`).

## Konteks penting (baca dulu sebelum ngoding)

Hierarki organisasi = 4 level: **National (1) → Area (2) → Regional (3) → District (4)**.
Struktur organisasi, tipe, dan role SUDAH ada di seeder dan TIDAK boleh dihapus:

- `RoleSeeder.php` — role (`admin_sup`, `admin_nas`, `admin_are`, `admin_reg`, `admin_dis`)
- `OrganizationTypeSeeder.php` — tipe & level
- `OrganizationSeeder.php` — semua organisasi (National, Area, Regional, District)
- `AssessmentQuestionSeeder.php` — soal assessment

> **"Hapus semua seeder" di sini artinya hanya buang data USER dummy**, bukan struktur
> organisasi/role. Tanpa organisasi & role, user piloting tidak punya tempat menempel.
> Jadi yang diganti hanya bagian pembuatan user.

Yang DIHAPUS/DIGANTI:
- `DummyUserSeeder.php` — hapus, ganti dengan `PilotingUserSeeder.php`
- Blok `User::factory()->create([...])` "Test User" di dalam `DatabaseSeeder.php`

## Daftar user yang harus dibuat (12 user)

Semua pakai password default: **`password`** (min 8 karakter, sesuai validasi register).

| Username | Organisasi (sname) | Role (sname) |
|---|---|---|
| `admin_district_binjai` | BINJAI (`binj`) | `admin_dis` |
| `admin_district_karawang` | KARAWANG (`krwa`) | `admin_dis` |
| `admin_district_flores` | FLORES (`flrs`) | `admin_dis` |
| `admin_district_makassar` | MAKASSAR (`mksr`) | `admin_dis` |
| `admin_regional_sumbagut` | REGIONAL SUMBAGUT (`smut`) | `admin_reg` |
| `admin_regional_eastern_jabotabek` | REGIONAL EASTERN JABOTABEK (`esja`) | `admin_reg` |
| `admin_regional_bali_nusra` | REGIONAL BALI NUSRA (`banu`) | `admin_reg` |
| `admin_regional_sulawesi` | REGIONAL SULAWESI (`sula`) | `admin_reg` |
| `admin_area_1` | AREA I - SUMATERA (`tif1`) | `admin_are` |
| `admin_area_2` | AREA II - JABODETABEK JABAR (`tif2`) | `admin_are` |
| `admin_area_3` | AREA III - JAWA BALI (`tif3`) | `admin_are` |
| `admin_area_4` | AREA IV - PAMASUKA (`tif4`) | `admin_are` |
| `admin_nasional` | TELKOM INFRASTUKTUR INDONESIA (`tif`) | `admin_nas` |

Plus **1 user superadmin** untuk login mengelola aplikasi (login sekarang pakai username):

| Username | Organisasi | Role |
|---|---|---|
| `superadmin` | — (null) | `admin_sup` |

> Total = 14 user (12 piloting + 1 nasional + 1 superadmin).

## Tahapan implementasi

### Langkah 1 — Buat file seeder baru

Buat file `backend/database/seeders/PilotingUserSeeder.php` dengan isi berikut
(copy apa adanya):

```php
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
```

Catatan: pakai `User::factory()->create([...])` (bukan `forceCreate`) karena factory sudah
mengisi `uuid`, `nik`, dll otomatis — kita cuma override field yang perlu. Ini pola yang sama
dengan `DummyUserSeeder` lama.

### Langkah 2 — Update `DatabaseSeeder.php`

Buka `backend/database/seeders/DatabaseSeeder.php`. Hapus blok pembuatan "Test User" dan
ganti pemanggilan `DummyUserSeeder` jadi `PilotingUserSeeder`. Hasil akhir `run()`:

```php
public function run(): void
{
    $this->call(RoleSeeder::class);
    $this->call(OrganizationTypeSeeder::class);
    $this->call(OrganizationSeeder::class);
    $this->call(AssessmentQuestionSeeder::class);

    $this->call(PilotingUserSeeder::class);
}
```

Hapus juga `use App\Models\User;` dan `use App\Models\Role;` di atas file kalau sudah tidak
dipakai lagi (biar tidak ada import nganggur).

### Langkah 3 — Hapus seeder lama

Hapus file `backend/database/seeders/DummyUserSeeder.php`.

### Langkah 4 — Reset & seed ulang database

Dari folder `backend`, jalankan:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` mengosongkan semua tabel lalu migrasi ulang, `--seed` menjalankan
`DatabaseSeeder`. Ini otomatis menghapus semua user lama (termasuk 158 user dummy sebelumnya).

## Verifikasi (acceptance criteria)

1. Jumlah user tepat **14**:
   ```bash
   php artisan tinker --execute="echo \App\Models\User::count();"
   ```
   Harus keluar `14`.

2. Cek satu user district benar organisasi & role-nya:
   ```bash
   php artisan tinker --execute="\$u=\App\Models\User::where('username','admin_district_makassar')->with('role','organization')->first(); echo \$u->role->sname.' | '.\$u->organization->name;"
   ```
   Harus keluar `admin_dis | MAKASSAR`.

3. Login lewat aplikasi pakai `superadmin` / `password` berhasil masuk ke dashboard.
4. Login pakai salah satu `admin_district_*` / `password` juga berhasil.

## Yang TIDAK boleh dilakukan

- Jangan hapus `RoleSeeder`, `OrganizationTypeSeeder`, `OrganizationSeeder`,
  `AssessmentQuestionSeeder` — itu data struktur, bukan user dummy.
- Jangan ubah skema tabel / migration. Kolom `username` sudah ada dan `unique`.
- Jangan hardcode `organization_id`/`role_id` berupa angka — selalu lookup lewat `sname`,
  karena id bisa berbeda tiap kali `migrate:fresh`.
