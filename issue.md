# Issue: Dummy User Seeder & Penambahan Field pada Form Add User

> **Untuk implementor (junior programmer / model AI):** Ikuti tahapan di bawah **berurutan**. Setiap tahap punya checklist dan contoh kode. Jangan mengubah file di luar yang disebutkan. Setelah selesai, jalankan langkah verifikasi di bagian akhir.

## Konteks Singkat (baca dulu)

Stack:
- **Backend**: Laravel (folder [backend/](backend/)). Primary key tabel pakai nama custom (`user_id`, `role_id`, `organization_id`, `organization_type_id`), **bukan** `id`.
- **Frontend**: Vue (folder [frontend/](frontend/)). Halaman user management ada di [frontend/src/views/pages/users.vue](frontend/src/views/pages/users.vue).

Relasi penting:
- `organizations` punya `organization_type_id` → `organization_types` (punya kolom `level`: National=1, Area=2, Regional=3, District=4).
- `users` punya `role_id` → `roles`, dan `organization_id` → `organizations`.

**Pemetaan tipe organisasi → role** (sumber: [RoleSeeder.php](backend/database/seeders/RoleSeeder.php) & [OrganizationTypeSeeder.php](backend/database/seeders/OrganizationTypeSeeder.php)):

| Tipe Organisasi | level | sname role | Nama role     |
|-----------------|-------|------------|---------------|
| National        | 1     | `admin_nas`| ADMIN NASIONAL|
| Area            | 2     | `admin_are`| ADMIN AREA    |
| Regional        | 3     | `admin_reg`| ADMIN REGIONAL|
| District        | 4     | `admin_dis`| ADMIN DISTRICT|

---

## BAGIAN 1 — Seeder User Dummy

### Tujuan
Untuk **setiap organisasi**, buat **2 user dummy**, dan **kedua user memakai role yang sama dengan tipe organisasinya sendiri** (lihat tabel pemetaan di atas).

Contoh hasil yang diharapkan:

| User | Role | Organisasi |
|------|------|------------|
| User A | ADMIN NASIONAL | TELKOM INFRASTUKTUR INDONESIA |
| User B | ADMIN NASIONAL | TELKOM INFRASTUKTUR INDONESIA |
| User C | ADMIN AREA | AREA I - SUMATERA |
| User D | ADMIN AREA | AREA I - SUMATERA |
| … | ADMIN AREA | … sampai AREA IV - PAMASUKA (masing-masing 2 user) |
| User X | ADMIN REGIONAL | REGIONAL SUMBAGUT |
| User XX | ADMIN REGIONAL | REGIONAL SUMBAGUT |
| … | ADMIN REGIONAL | … sampai REGIONAL MALUKU PAPUA (masing-masing 2 user) |
| User Y | ADMIN DISTRICT | BANDA ACEH |
| User YY | ADMIN DISTRICT | BANDA ACEH |
| … | ADMIN DISTRICT | … sampai JAYAPURA (masing-masing 2 user) |

Jadi: **tidak ada kasus khusus** — semua organisasi (National, Area, Regional, District) sama-sama dapat **tepat 2 user** dengan role sesuai tipenya.

Semua field diisi **acak (random)**: `username`, `name`, `nik`, `email`, `phone_number`, `password`, `is_ldap`, `is_active`.

### Tahapan

**1.1 — Tambahkan state random ke [UserFactory.php](backend/database/factories/UserFactory.php)**

Saat ini factory belum mengisi `nik`, `phone_number`, `is_ldap`, `is_active`. Lengkapi `definition()` agar mengisi semua field random tersebut. NIK diisi **6 angka random** dan harus unik (kolom `nik` di DB `unique`).

```php
public function definition(): array
{
    return [
        'uuid' => Str::uuid(),
        'username' => fake()->unique()->userName(),
        'name' => fake()->name(),
        'nik' => fake()->unique()->numerify('######'), // 6 angka random
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'phone_number' => fake()->numerify('08##########'),
        'password' => static::$password ??= Hash::make('password'),
        'is_ldap' => fake()->boolean(),
        'is_active' => fake()->boolean(90), // ~90% aktif
        'remember_token' => Str::random(10),
    ];
}
```

> Catatan: jangan hapus baris `static::$password ??= Hash::make('password')` — itu agar semua dummy punya password sama (`password`) supaya mudah login saat testing.

**1.2 — Buat file seeder baru `DummyUserSeeder.php`**

Lokasi: `backend/database/seeders/DummyUserSeeder.php`.

Logika:
1. Petakan `level` tipe organisasi ke `sname` role (sesuai tabel di atas), lalu ambil `role_id` tiap level.
2. Ambil semua organisasi beserta `type` (eager load `with('type')`).
3. Untuk tiap organisasi: buat **2 user** via factory, **keduanya** dengan `organization_id` = `$org->organization_id` dan `role_id` sesuai `level` organisasi itu sendiri.

Contoh implementasi:

```php
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
```

**1.3 — Daftarkan seeder di [DatabaseSeeder.php](backend/database/seeders/DatabaseSeeder.php)**

Panggil `DummyUserSeeder` **setelah** `OrganizationSeeder` (karena butuh data organisasi & role sudah ada), dan setelah pembuatan `Test User`:

```php
$this->call(RoleSeeder::class);
$this->call(OrganizationTypeSeeder::class);
$this->call(OrganizationSeeder::class);

User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'role_id' => Role::where('sname', 'admin_sup')->first()?->role_id,
]);

$this->call(DummyUserSeeder::class);
```

> Penting: `DatabaseSeeder` memakai `WithoutModelEvents`, jadi event `creating` di model User (yang auto-generate `uuid`) **tidak jalan**. Karena itu factory **wajib** mengisi `uuid` sendiri (sudah ada di factory, jangan dihapus).

**1.4 — Jalankan & cek**

```bash
cd backend
php artisan migrate:fresh --seed
```

Pastikan tidak ada error. Lalu cek jumlah user: harus ada **2 user per organisasi** (semua tipe) + 1 Test User. Contoh: jika ada 78 organisasi → 78 × 2 = 156 user dummy + 1 Test User = 157.

---

## BAGIAN 2 — Tambah Field pada Form "Add User"

### Tujuan
Pada form Add User di [users.vue](frontend/src/views/pages/users.vue), tambahkan input:
1. **NIK** (text)
2. **is_LDAP** (checkbox)
3. **Organisasi** (dropdown, ambil dari API `/organizations`)

Plus update **backend** agar menerima & menyimpan field-field tersebut.

### Tahapan

**2.1 — Backend: izinkan field baru di [UserController.php](backend/app/Http/Controllers/Api/UserController.php) method `store()`**

Tambahkan validasi untuk `nik`, `is_ldap`, `organization_id`:

```php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'username' => ['required', 'string', 'max:255', 'unique:users,username'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
    'nik' => ['nullable', 'string', 'max:16', 'unique:users,nik'],
    'phone_number' => ['nullable', 'string', 'max:20'],
    'password' => ['required', 'string', 'min:8'],
    'is_ldap' => ['sometimes', 'boolean'],
    'role_id' => ['nullable', 'exists:roles,role_id'],
    'organization_id' => ['nullable', 'exists:organizations,organization_id'],
]);
```

> `nik`, `is_ldap`, `organization_id` sudah ada di `#[Fillable]` model [User.php](backend/app/Models/User.php), jadi `User::create($validated)` otomatis menyimpannya. Tidak perlu ubah model.

> (Opsional, kalau ingin konsisten) tambahkan juga `nik`, `is_ldap`, `organization_id` ke validasi method `update()` dengan aturan `sometimes`. Untuk issue ini fokus ke `store()` saja sudah cukup.

**2.2 — Frontend: ambil daftar organisasi**

Di [users.vue](frontend/src/views/pages/users.vue), tambahkan:
- state `organizations: []` di `data()`.
- method `fetchOrganizations()` yang memanggil `api.get('/organizations')`.
- panggil method itu di `mounted()` (sejajar `fetchUsers()` dan `fetchRoles()`).

```js
// di data()
organizations: [],

// method baru (pola sama seperti fetchRoles)
async fetchOrganizations() {
    try {
        const { data } = await api.get('/organizations');
        this.organizations = data.data;
    } catch (error) {
        // abaikan; dropdown organisasi akan kosong
    }
},

// di mounted()
this.fetchOrganizations();
```

**2.3 — Frontend: tambahkan field ke object `form`**

Di `data().form` dan di `openAdd()` (keduanya harus konsisten), tambahkan `nik`, `is_ldap`, `organization_id`:

```js
form: {
    user_id: null, name: "", username: "", email: "",
    password: "", phone_number: "", role_id: "", is_active: true,
    nik: "", is_ldap: false, organization_id: "",
},
```

Lakukan penambahan yang sama di dalam `openAdd()` (yang me-reset `this.form`).

**2.4 — Frontend: tambahkan input di modal "Add User"**

Di dalam `<BModal v-model="showAdd" ...>` (sekitar baris 194–227), tambahkan 3 blok input. Letakkan **NIK** setelah Email, **Organisasi** setelah Role, dan **is_LDAP** sebagai checkbox sebelum tombol Save:

```html
<!-- NIK -->
<div class="mb-2">
    <label class="form-label mb-1">NIK</label>
    <input type="text" class="form-control" v-model="form.nik" maxlength="16">
</div>

<!-- Organisasi -->
<div class="mb-2">
    <label class="form-label mb-1">Organisasi</label>
    <select class="form-control" v-model="form.organization_id">
        <option value="">— pilih organisasi —</option>
        <option v-for="org in organizations" :key="org.organization_id" :value="org.organization_id">
            {{ org.name }}
        </option>
    </select>
</div>

<!-- is_LDAP -->
<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" id="addUserLdap" v-model="form.is_ldap">
    <label class="form-check-label" for="addUserLdap">LDAP</label>
</div>
```

**2.5 — Frontend: kirim field baru di `createUser()`**

Update payload `api.post('/users', {...})` agar menyertakan field baru:

```js
await api.post('/users', {
    name: this.form.name,
    username: this.form.username,
    email: this.form.email,
    password: this.form.password,
    nik: this.form.nik || null,
    phone_number: this.form.phone_number,
    is_ldap: this.form.is_ldap,
    role_id: this.form.role_id || null,
    organization_id: this.form.organization_id || null,
});
```

---

## Verifikasi (wajib dilakukan setelah implementasi)

**Bagian 1 (seeder):**
1. `cd backend && php artisan migrate:fresh --seed` → tidak ada error.
2. Cek di DB / tinker bahwa tiap organisasi punya user dengan `role_id` & `organization_id` benar:
   ```bash
   php artisan tinker
   >>> \App\Models\User::with('role','organization.type')->get()->groupBy('organization_id')->map->count();
   ```
   Setiap organisasi (semua tipe) = 2 user, dengan `role` sesuai tipe organisasinya.
3. Pastikan field `nik`, `phone_number`, `is_ldap`, `is_active` terisi (tidak null) dan `nik` berupa 6 angka.

**Bagian 2 (form Add User):**
1. Jalankan backend (`php artisan serve`) & frontend (`npm run serve` di folder `frontend`).
2. Buka halaman User Management → klik **Add User**.
3. Pastikan muncul input **NIK**, dropdown **Organisasi**, dan checkbox **LDAP**.
4. Isi semua field, Save → user baru muncul di tabel, dan di DB kolom `nik`, `is_ldap`, `organization_id` tersimpan benar.
5. Coba simpan NIK yang sama dua kali → backend menolak (error unique), tidak crash.

---

## Ringkasan File yang Disentuh

| File | Aksi |
|------|------|
| [backend/database/factories/UserFactory.php](backend/database/factories/UserFactory.php) | Tambah field random (`nik`, `phone_number`, `is_ldap`, `is_active`) |
| `backend/database/seeders/DummyUserSeeder.php` | **Buat baru** — seeder user dummy per organisasi |
| [backend/database/seeders/DatabaseSeeder.php](backend/database/seeders/DatabaseSeeder.php) | Daftarkan `DummyUserSeeder` |
| [backend/app/Http/Controllers/Api/UserController.php](backend/app/Http/Controllers/Api/UserController.php) | Tambah validasi `nik`, `is_ldap`, `organization_id` di `store()` |
| [frontend/src/views/pages/users.vue](frontend/src/views/pages/users.vue) | Tambah state organisasi, field form, input NIK/Organisasi/LDAP, payload `createUser()` |

## Catatan / Hal yang Tidak Boleh Dilakukan
- **Jangan** menampilkan/menghapus kolom `sname` (dipakai logika di backend).
- **Jangan** mengirim `password` saat update user (backend menolaknya — lihat komentar di `saveEdit()`).
- **Jangan** mengubah primary key model atau struktur tabel di luar yang diminta.
- Pertahankan baris auto-isi `uuid` di factory; tanpa itu seeder akan gagal (karena `WithoutModelEvents`).
