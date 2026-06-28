# Issue: Rombak UI Tabel User Management & Role Management

> **Untuk implementor (junior / AI):** Kerjakan **berurutan**. Semua perubahan ada di folder `frontend/` — **tidak ada perubahan backend**. Tiru pola dari file referensi yang path-nya selalu disebut; jangan mengarang pola baru. Setiap bagian punya checklist **Definition of Done (DoD)**.

---

## 0. Konteks & Ruang Lingkup

### File yang DIUBAH
- [frontend/src/views/pages/users.vue](frontend/src/views/pages/users.vue) — halaman User Management (sudah ada, CRUD-nya sudah jalan).
- [frontend/src/views/pages/roles.vue](frontend/src/views/pages/roles.vue) — halaman Role Management (sudah ada, CRUD-nya sudah jalan).

### File REFERENSI (hanya untuk meniru desain — JANGAN diubah)
- [frontend/src/views/live-preview/admins/course-teacher-list.vue](frontend/src/views/live-preview/admins/course-teacher-list.vue) — desain **card + header dengan tombol "Add" & "Filter"**, struktur tabel.
- [frontend/src/views/live-preview/application/users/user-list.vue](frontend/src/views/live-preview/application/users/user-list.vue) — desain **icon action** (pensil bg biru, trash bg putih merah).
- [frontend/src/views/live-preview/admins/course-teacher-apply.vue](frontend/src/views/live-preview/admins/course-teacher-apply.vue) — desain tombol **"Apply/Filter"** (`btn btn-outline-secondary`).
- [frontend/src/views/components/basic/modal.vue](frontend/src/views/components/basic/modal.vue) — contoh penggunaan **`<BModal>`** (untuk form Add/Edit/View).

### Yang TIDAK boleh dikerjakan
- Tidak ada perubahan backend (controller, model, migration, route).
- Tidak menambah/menghapus halaman lain.

---

## 1. ⚠️ Aturan WAJIB (baca dulu, ini inti dari issue)

1. **JANGAN tampilkan `id` (PK) di UI.** Hapus kolom "ID". Ganti dengan kolom **`#`** yang berisi **nomor urut baris** (1, 2, 3, …) mengikuti urutan tampil. Setelah delete atau filter, `#` **otomatis menyesuaikan** (karena dihitung dari index baris yang sedang tampil, bukan dari `id`).
   - PK (`user_id` / `role_id`) **tetap dipakai di balik layar** untuk edit/view/delete (URL API & pembanding baris) — hanya **tidak ditampilkan**.
2. **Role table: JANGAN tampilkan `sname`.** `sname` dipakai backend untuk logika otorisasi/limitasi user, jadi tidak boleh diekspos di tabel. **TAPI** `sname` **tetap wajib ada di form Add Role** karena `POST /roles` mengharuskannya (lihat catatan di Task C).
3. **Kolom `Organisasi` (user table) belum punya sumber data.** Backend punya kolom `organization_id` tapi **tabel `organizations` belum dibuat** dan API **tidak** mengembalikan nama organisasi (relasi belum di-load). Jadi:
   - Tetap buat kolomnya (sesuai spek), tapi tampilkan **`-`** sebagai placeholder untuk sementara.
   - Tambahkan komentar `<!-- TODO: tampilkan nama organisasi setelah modul Organizations dibuat -->`.
   - **Jangan** menampilkan angka `organization_id` mentah (itu sama saja mengekspos id).

---

## 2. Hal yang dipakai berulang (pelajari sekali, pakai di kedua halaman)

Kedua halaman pakai pola yang sama: ambil data via `api` service (`import api from "@/services/api"`), response berbentuk `{ data: [...] }` (baca `data.data`), PK `user_id`/`role_id`. **Pertahankan logika CRUD yang sudah ada** — yang dirombak adalah **tampilan + search + filter + cara Add/Edit/View**.

### 2a. Desain tabel (border jelas + hover)
Bungkus dengan card bergaya `course-teacher-list.vue`: `card table-card` → `card-header` (judul + tombol) → `card-body pt-3` → `table-responsive`. Tabelnya:

```html
<table class="table table-hover table-bordered align-middle">
```

`table-bordered` = garis batas antar **baris dan kolom** terlihat jelas (ini yang diminta "both border").

### 2b. Header card: judul + tombol Filter + tombol Add
Tiru header `course-teacher-list.vue` (tombol kanan-atas):

```html
<div class="card-header">
    <div class="d-sm-flex align-items-center justify-content-between">
        <h5 class="mb-3 mb-sm-0">User Management</h5>
        <div class="d-flex gap-2">
            <input type="text" class="form-control" style="min-width: 220px"
                   placeholder="Search..." v-model="search">
            <button class="btn btn-outline-secondary" @click="showFilter = !showFilter">
                <i class="ti ti-filter f-18"></i> Filter
            </button>
            <button class="btn btn-primary" @click="openAdd">
                <i class="ti ti-plus f-18"></i> Add User
            </button>
        </div>
    </div>
</div>
```

- Tombol **Add** pakai gaya `btn btn-primary` (= desain "Add Teacher").
- Tombol **Filter** pakai gaya `btn btn-outline-secondary` (= desain "Apply Teacher List").
- Untuk Role page, ganti label jadi "Role Management" / "Add Role".

### 2c. Search di SEMUA kolom (live)
Buat computed `filteredX` yang memfilter berdasarkan `search` (case-insensitive, substring) terhadap **semua kolom yang ditampilkan**. Contoh untuk roles (ketik "admin" → semua role mengandung "admin" muncul):

```js
computed: {
    filteredRoles() {
        let rows = this.roles;
        // (filter dropdown diterapkan di sini juga — lihat 2d)
        const q = this.search.trim().toLowerCase();
        if (q) {
            rows = rows.filter(r =>
                [r.name, r.description, r.is_active ? 'active yes' : 'inactive no']
                    .join(' ').toLowerCase().includes(q)
            );
        }
        return rows;
    }
}
```

> **Penting:** susun string pencarian dari **teks yang ditampilkan**, termasuk nilai turunan seperti `'Active'/'Yes'` supaya ketik "active" juga cocok. Untuk user, gabungkan: `username`, `name`, `role?.name`, teks LDAP (`is_ldap ? 'ldap yes' : 'no'`), teks Active. **Jangan** masukkan `sname` ke pencarian role (tidak ditampilkan & tidak boleh diekspos).

### 2d. Filter panel (toggle oleh tombol Filter)
Panel sederhana yang muncul saat `showFilter` true, di atas tabel (`v-if="showFilter"`). Filter diterapkan di dalam computed `filteredX` (sebelum search). Minimal:
- **User page:** dropdown **Role** (opsi dari `roles`), **Status** (Semua / Active / Inactive), **LDAP** (Semua / Ya / Tidak).
- **Role page:** dropdown **Status** (Semua / Active / Inactive).

Pakai `v-model` ke state mis. `filter.role_id`, `filter.active`, `filter.ldap`, lalu di computed: kalau nilainya kosong/"all" → lewati.

### 2e. Kolom `#` (nomor urut, bukan id)
Iterasi pakai index dari list yang **sudah difilter**, tampilkan `index + 1`:

```html
<tr v-for="(user, index) in filteredUsers" :key="user.user_id">
    <td>{{ index + 1 }}</td>
    ...
```

Karena `#` dihitung dari `index` list tampil, setelah delete/filter otomatis urut ulang 1..n.

### 2f. Kolom Actions (icon: view / edit / delete)
Tiru gaya icon dari `user-list.vue`, tambah tombol **view (mata) bg orange**:

```html
<td>
    <ul class="list-inline mb-0 text-end">
        <li class="list-inline-item m-0">
            <a href="#" class="avtar avtar-s btn btn-warning" @click.prevent="openView(user)">
                <i class="ti ti-eye f-18"></i>
            </a>
        </li>
        <li class="list-inline-item m-0">
            <a href="#" class="avtar avtar-s btn btn-primary" @click.prevent="openEdit(user)">
                <i class="ti ti-pencil f-18"></i>
            </a>
        </li>
        <li class="list-inline-item m-0">
            <a href="#" class="avtar avtar-s btn bg-white btn-link-danger" @click.prevent="deleteUser(user)">
                <i class="ti ti-trash f-18"></i>
            </a>
        </li>
    </ul>
</td>
```

- **Pensil bg biru** (`btn btn-primary`) = edit.
- **Mata bg orange** (`btn btn-warning`) = view detail.
- **Trash putih/merah** (`bg-white btn-link-danger`) = delete.
- Icon pakai Tabler (`ti ti-*`) — sudah tersedia di project.

### 2g. Add / Edit / View pakai `<BModal>`
`BModal` dari `bootstrap-vue-next` sudah terpasang global (lihat [modal.vue](frontend/src/views/components/basic/modal.vue)). Ganti **inline edit** lama dengan 3 modal:
- **Add modal** — form kosong, tombol "Save" → `createX()`.
- **Edit modal** — form ter-isi data baris (shallow copy), tombol "Save" → `saveEdit()`. (User: **jangan** sertakan field password.)
- **View modal** — read-only, tampilkan semua field baris (boleh termasuk yang tidak ada di tabel, mis. email/phone), **kecuali** PK id dan `sname`.

Skeleton modal (contoh Add User):

```html
<BModal v-model="showAdd" title="Add User" @ok="createUser" ok-title="Save">
    <div class="mb-2"><input class="form-control" placeholder="Username" v-model="form.username"></div>
    <div class="mb-2"><input class="form-control" placeholder="Name" v-model="form.name"></div>
    <div class="mb-2"><input type="email" class="form-control" placeholder="Email" v-model="form.email"></div>
    <div class="mb-2"><input type="password" class="form-control" placeholder="Password (min 8)" v-model="form.password"></div>
    <div class="mb-2"><input class="form-control" placeholder="Phone" v-model="form.phone_number"></div>
    <div class="mb-2">
        <select class="form-control" v-model="form.role_id">
            <option value="">— pilih role —</option>
            <option v-for="r in roles" :key="r.role_id" :value="r.role_id">{{ r.name }}</option>
        </select>
    </div>
</BModal>
```

Method `openAdd()` = reset `form` lalu `showAdd = true`. `openEdit(row)` = `form = { ...row }` lalu `showEdit = true`. `openView(row)` = `viewRow = row` lalu `showView = true`.

### 2h. Konfirmasi delete (opsional tapi disarankan)
`vue-sweetalert2` sudah terpasang. Boleh pakai `this.$swal({...})` untuk konfirmasi sebelum hapus, atau minimal `confirm()` bawaan browser. Jangan hapus tanpa konfirmasi.

### DoD — Bagian umum (berlaku di kedua halaman)
- [ ] Tidak ada kolom "ID"/id mentah di tabel; ada kolom `#` berurutan yang menyesuaikan setelah delete/filter.
- [ ] Tabel pakai `table-bordered` (garis antar baris & kolom terlihat).
- [ ] Tombol **Add** (primary) & **Filter** (outline-secondary) ada di header card.
- [ ] Search box memfilter **semua kolom yang ditampilkan**, live & case-insensitive (uji: ketik "admin" di Role page → semua role ber-"admin" tampil).
- [ ] Filter panel berfungsi (status/role/ldap sesuai halaman).
- [ ] Kolom Actions: icon **mata (orange)** = view, **pensil (biru)** = edit, **trash (putih/merah)** = delete.
- [ ] Add/Edit/View lewat modal; delete pakai konfirmasi.
- [ ] `npm run build` sukses, tidak ada error console di browser.

---

## 3. Task C — roles.vue

### Kolom tabel (urut): `#`, **Nama**, **Description**, **Active**, **Actions**
- **Nama** = `role.name`.
- **Description** = `role.description`.
- **Active** = badge: `is_active` → `<span class="badge bg-light-success">Active</span>` / `<span class="badge bg-light-danger">Inactive</span>` (gaya badge lihat `user-list.vue`).
- **JANGAN** ada kolom `#`→id, dan **JANGAN** ada kolom `sname`.

### Catatan `sname` di form Add Role (WAJIB)
`POST /roles` memvalidasi `sname` sebagai **required & unique**. Jadi **form Add Role tetap punya input `sname`** (mis. `admin_xxx`). Yang dilarang hanya **menampilkannya di tabel**. Di **Edit Role**, `sname` **tidak** dikirim (backend `update` memang tidak menerimanya) — Edit hanya `name`, `description`, `is_active`.

### DoD — Task C
- [ ] Kolom persis: `#`, Nama, Description, Active, Actions (tanpa id, tanpa sname).
- [ ] Add Role (modal) tetap mengirim `sname` dan berhasil membuat role.
- [ ] Edit Role mengubah name/description/is_active; tidak mengirim sname.
- [ ] Search & filter status berfungsi.

---

## 4. Task D — users.vue

### Kolom tabel (urut): `#`, **Username**, **Nama**, **Organisasi**, **Role**, **LDAP**, **Active**, **Actions**
- **Username** = `user.username`.
- **Nama** = `user.name`.
- **Organisasi** = tampilkan `-` (lihat Aturan Wajib #3 — belum ada datanya).
- **Role** = `user.role?.name`.
- **LDAP** = badge dari `user.is_ldap` (boolean dari API): `Ya`/`Tidak` (atau badge success/secondary).
- **Active** = badge dari `user.is_active` (Active/Inactive).
- Tanpa kolom id.

### Catatan field
- **Add User** (modal): kirim `name`, `username`, `email`, `password` (wajib, min 8), `phone_number?`, `role_id?` — sesuai `POST /users`.
- **Edit User** (modal): kirim `name?`, `username?`, `email?`, `phone_number?`, `is_active?`, `role_id?` — **TANPA `password`** (backend `update` tidak menerimanya).
- PK = `user_id` (untuk URL edit/view/delete).

### DoD — Task D
- [ ] Kolom persis: `#`, Username, Nama, Organisasi, Role, LDAP, Active, Actions (tanpa id).
- [ ] Organisasi menampilkan `-` + ada komentar TODO.
- [ ] LDAP & Active tampil sebagai badge yang benar sesuai data.
- [ ] Add/Edit/View/Delete berfungsi; Edit tidak mengirim password.
- [ ] Search & filter (role/status/ldap) berfungsi.

---

## 5. Verifikasi akhir
1. Jalankan backend (`php artisan serve`, port 8000) & frontend (`npm run serve`, port 8080). Login `test@example.com` / `password`.
2. **Role page** (`/roles`): kolom benar (tanpa id/sname), Add via modal (dengan sname) berhasil, Edit/View/Delete jalan, search "admin" memfilter, filter status jalan.
3. **User page** (`/users`): kolom benar (tanpa id), Organisasi `-`, LDAP/Active badge benar, Add/Edit/View/Delete jalan (Edit tanpa password), search & filter jalan, `#` urut ulang setelah delete.
4. `npm run build` sukses tanpa error. Tidak ada error di console browser.

## 6. Tips & jebakan
- **Jangan import axios langsung** — pakai `import api from "@/services/api"`.
- **PK bukan `id`**: user `user_id`, role `role_id`. Dipakai untuk API, **tidak** ditampilkan.
- **`#` = index+1 dari list terfilter**, bukan dari id — supaya auto-urut setelah delete/filter.
- **`sname`**: hilang dari tabel, tapi wajib di form Add Role.
- **Organisasi**: belum ada datanya → `-` (jangan tampilkan angka organization_id).
- **Edit user**: jangan kirim `password`.
- Setelah Add/Edit/Delete sukses, panggil ulang `fetchUsers()`/`fetchRoles()` agar tabel sinkron (pola lama sudah begitu).
- Pertahankan Options API & gaya kode `roles.vue`/`users.vue` yang sekarang.

## 7. Saran pemecahan PR
- PR-1: roles.vue (Task C + bagian umum).
- PR-2: users.vue (Task D + bagian umum).

Boleh juga 1 PR untuk keduanya kalau perubahannya rapi — tapi pisah lebih mudah di-review.
