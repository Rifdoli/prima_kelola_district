# Issue: Organization Management — Filter & Tombol Move (Frontend)

> **Untuk implementor (junior / AI):** Kerjakan **berurutan** dari Tahap 1. **Semua perubahan ada di satu file**: [frontend/src/views/pages/organizations.vue](frontend/src/views/pages/organizations.vue) — **tidak ada perubahan backend** (API `move` sudah jadi) dan **tidak ada file komponen yang diubah**. **Tiru pola dari file referensi** yang path-nya disebut; jangan mengarang pola baru. Setiap tahap punya checklist **Definition of Done (DoD)**. Jika ragu, **berhenti dan tanya**.

---

## 0. Konteks & Ruang Lingkup

Halaman **Organization Management** (`/organizations`) sudah jadi: menampilkan tabel 78 organisasi dengan Add / Edit / View / Delete (lihat [organizations.vue](frontend/src/views/pages/organizations.vue) yang sekarang). Issue ini menambahkan **dua fitur** ke halaman itu:

1. **Filter** — tombol "Filter" yang membuka modal untuk menyaring tabel berdasar **Organization Type** dan **Status (Active/Inactive)**.
2. **Tombol Move** — aksi baru di kolom Actions tiap baris untuk **memindahkan organisasi ke induk (parent) lain**, berupa modal berisi dropdown pilihan parent baru.

Keduanya **sudah pernah ditulis sebagai Tahap 6 (Move) & Tahap 7 (Filter) opsional** di issue halaman Organizations sebelumnya — sekarang dikerjakan sebagai issue tersendiri.

### File yang DIUBAH (hanya satu)
- [frontend/src/views/pages/organizations.vue](frontend/src/views/pages/organizations.vue) — semua pekerjaan ada di sini.

### File REFERENSI (tiru polanya — JANGAN diubah)
- [frontend/src/views/pages/users.vue](frontend/src/views/pages/users.vue) — **contoh lengkap pola Filter**: data `filter`, computed `filteredUsers`, modal Filter, method `resetFilter`, dan `:rows="filteredUsers"`. Tiru persis untuk Filter di sini.
- [frontend/src/components/common/RowActions.vue](frontend/src/components/common/RowActions.vue) — komponen tombol aksi. **Punya slot `#extra`** (`<slot name="extra" :row="row">`) — **di situlah** tombol Move dipasang. **Jangan** menambah `'move'` ke prop `actions`; `actionMap` di komponen ini hanya kenal view/edit/delete, jadi pakai slot `#extra` agar tidak perlu mengubah komponen.
- [frontend/src/services/api.js](frontend/src/services/api.js) — axios instance (base URL + token otomatis). Pakai ini untuk semua request.

### Yang TIDAK boleh dikerjakan
- Tidak mengubah backend (controller/model/migration/route) — endpoint `move` **sudah ada**.
- Tidak mengubah file komponen ([RowActions.vue](frontend/src/components/common/RowActions.vue), [AdminDataTable.vue](frontend/src/components/common/AdminDataTable.vue)) atau file referensi.
- Tidak mengubah modal Edit untuk menambah pindah-parent — **pindah parent KHUSUS lewat tombol Move** (modal tersendiri). Edit tetap tidak menyentuh parent.

---

## 1. Kontrak API (sudah tersedia — JANGAN bikin endpoint baru)

Untuk Filter **tidak ada endpoint baru** — penyaringan dilakukan **di sisi frontend** terhadap data yang sudah di-`fetch` (pola identik dengan `users.vue`). Tipe organisasi sudah tersedia dari `this.types` (hasil `GET /organization-types`, sudah di-fetch oleh halaman).

Untuk Move, satu endpoint:

| Aksi | Request | Catatan |
|---|---|---|
| Pindah induk | `POST /organizations/{id}/move` | body: `{ "parent_organization_id": <id \| null> }`. `null` = jadikan organisasi root (tanpa induk). |

**Aturan & error endpoint `move` (status 422 dengan pesan di `error.response.data.message`):**
- Memindahkan organisasi **ke dirinya sendiri** → 422 `"An organization cannot be moved under itself."`
- Memindahkan organisasi **ke salah satu keturunannya sendiri** (akan membuat siklus) → 422 `"An organization cannot be moved under one of its own descendants."`
- Sukses → response `{ data, message: "Organization moved." }`, `data` sudah memuat relasi `type` & `parent` yang baru.

> **Penting:** frontend **tidak perlu** menghitung sendiri mana keturunan mana bukan. Tampilkan semua organisasi di dropdown (kecuali dirinya sendiri, lihat Tahap 5), lalu **andalkan 422 dari backend** untuk kasus siklus — tampilkan pesannya ke user. Pola ini sama dengan cara Delete menangani 422 cascade di halaman ini.

**DoD Tahap 1**
- [ ] Paham: Filter = murni frontend (tidak ada request baru); Move = satu request `POST .../move` yang bisa balas 422.

---

## 2. Filter — state & computed

Di [organizations.vue](frontend/src/views/pages/organizations.vue), tiru **persis** pola `filter` dari [users.vue](frontend/src/views/pages/users.vue).

1. Tambahkan ke `data()`:
   ```js
   showFilter: false,
   filter: { organization_type_id: "", active: "" },
   ```
2. Tambahkan blok `computed` (kalau belum ada `computed` di file ini, buat baru):
   ```js
   computed: {
       filteredOrganizations() {
           let rows = this.organizations;

           if (this.filter.organization_type_id) {
               rows = rows.filter(o =>
                   String(o.organization_type_id) === String(this.filter.organization_type_id));
           }
           if (this.filter.active === "active") {
               rows = rows.filter(o => o.is_active);
           } else if (this.filter.active === "inactive") {
               rows = rows.filter(o => !o.is_active);
           }

           return rows;
       }
   },
   ```
3. Tambahkan method `resetFilter`:
   ```js
   resetFilter() {
       this.filter = { organization_type_id: "", active: "" };
   },
   ```

**DoD Tahap 2**
- [ ] `filteredOrganizations` ada dan mengembalikan `this.organizations` apa adanya saat kedua filter kosong.

---

## 3. Filter — sambungkan ke tabel & tombol

1. Ganti sumber baris tabel dari `organizations` ke hasil filter:
   ```html
   <AdminDataTable title="Organization Table" :columns="columns" :rows="filteredOrganizations" :loading="loading"
       :search-keys="['name', 'sname', 'type.name', 'parent.name']">
   ```
   > Search bawaan `AdminDataTable` tetap berlaku **di atas** hasil filter (sama seperti users.vue) — tidak perlu utak-atik search.
2. Di slot `#header-actions`, tambahkan tombol Filter **sebelum** tombol Add (tiru users.vue):
   ```html
   <template #header-actions>
       <button class="btn btn-outline-secondary" @click="showFilter = true">
           <i class="ti ti-filter f-18"></i> Filter
       </button>
       <button class="btn btn-primary" @click="openAdd">
           <i class="ti ti-plus f-18"></i> Add Organization
       </button>
   </template>
   ```

**DoD Tahap 3**
- [ ] Tombol "Filter" tampil di kanan atas tabel, di sebelah "Add Organization".
- [ ] Tabel masih menampilkan 78 organisasi seperti semula (karena filter masih kosong).

---

## 4. Filter — modal

Tambahkan modal Filter (tiru struktur modal Filter di users.vue, tapi field-nya **Organization Type** & **Status**). Letakkan bersama modal-modal lain di dalam `<Layout>`:

```html
<!-- Filter -->
<BModal v-model="showFilter" title="Filter" hide-footer>
    <div class="mb-2">
        <label class="form-label mb-1">Organization Type</label>
        <select class="form-control" v-model="filter.organization_type_id">
            <option value="">Semua</option>
            <option v-for="type in types" :key="type.organization_type_id" :value="type.organization_type_id">
                {{ type.name }}
            </option>
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label mb-1">Status</label>
        <select class="form-control" v-model="filter.active">
            <option value="">Semua</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div class="text-end">
        <button class="btn btn-link-secondary" @click="resetFilter">Reset</button>
        <button class="btn btn-primary" @click="showFilter = false">Apply</button>
    </div>
</BModal>
```

> Dropdown type memakai `this.types` yang **sudah** di-fetch halaman (`fetchTypes()` di `mounted()`). Tidak perlu fetch baru.

**DoD Tahap 4**
- [ ] Pilih Organization Type "Regional" di filter → tabel hanya menampilkan 12 organisasi Regional.
- [ ] Pilih Status "Inactive" → hanya organisasi non-aktif yang tampil (jika tidak ada, tabel kosong — itu benar).
- [ ] Kombinasi Type + Status bekerja bersamaan (AND).
- [ ] Tombol **Reset** mengosongkan kedua filter → tabel kembali penuh.
- [ ] Search bar tetap berfungsi di atas hasil filter.

---

## 5. Move — tombol di kolom Actions

Pakai slot `#extra` dari [RowActions.vue](frontend/src/components/common/RowActions.vue) (komponen sudah menyediakannya — **jangan ubah komponennya**). Di slot `#actions` pada `AdminDataTable`, ubah pemakaian `RowActions` menjadi:

```html
<template #actions="{ row }">
    <RowActions :row="row" @view="openView" @edit="openEdit" @delete="deleteOrganization">
        <template #extra="{ row }">
            <li class="list-inline-item m-0">
                <a href="#" class="text-info" @click.prevent="openMove(row)" title="Move">
                    <i class="ti ti-arrows-right-left f-20"></i>
                </a>
            </li>
        </template>
    </RowActions>
</template>
```

> Struktur `<li class="list-inline-item m-0"><a ...><i class="... f-20"></i></a></li>` sengaja disamakan dengan markup tombol bawaan di RowActions agar ikon Move sejajar rapi dengan view/edit/delete.

**DoD Tahap 5**
- [ ] Tiap baris kini punya 4 ikon aksi: view (mata), edit (pensil), delete (tong sampah), **move (panah)**.
- [ ] Klik ikon Move membuka modal (diisi di Tahap 6) — belum harus berfungsi penuh di tahap ini.

---

## 6. Move — modal & logika submit

1. Tambahkan ke `data()`:
   ```js
   showMove: false,
   moveRow: null,        // organisasi yang sedang dipindah
   moveParentId: "",     // pilihan parent baru ("" = jadikan root/null)
   ```
2. Tambahkan **computed** untuk daftar pilihan parent (semua organisasi **kecuali dirinya sendiri**). Pakai computed, **jangan** `v-for`+`v-if` pada satu `<option>` — di Vue 3 `v-if` dievaluasi sebelum `v-for` sehingga tidak bisa membaca variabel loop:
   ```js
   // taruh di blok computed yang sama dengan filteredOrganizations
   moveParentOptions() {
       if (!this.moveRow) return this.organizations;
       return this.organizations.filter(
           o => o.organization_id !== this.moveRow.organization_id);
   },
   ```
3. Tambahkan method:
   ```js
   openMove(organization) {
       this.error = null;
       this.moveRow = organization;
       // default ke parent saat ini supaya user lihat posisi sekarang
       this.moveParentId = organization.parent_organization_id ?? "";
       this.showMove = true;
   },
   async submitMove() {
       try {
           await api.post(`/organizations/${this.moveRow.organization_id}/move`, {
               parent_organization_id: this.moveParentId || null,
           });
           this.showMove = false;
           this.fetchOrganizations();
       } catch (error) {
           // 422 = pindah ke diri sendiri / keturunan sendiri (siklus)
           this.error = error.response?.data?.message || 'Failed to move organization.';
       }
   },
   ```
4. Tambahkan modal Move (bersama modal lain). Dropdown parent di-loop dari computed `moveParentOptions` (sudah membuang dirinya sendiri), plus opsi root:
   ```html
   <!-- Move Organization -->
   <BModal v-model="showMove" title="Move Organization" hide-footer>
       <div class="alert alert-danger" v-if="error">{{ error }}</div>
       <div v-if="moveRow">
           <p class="mb-2">
               Memindahkan: <strong>{{ moveRow.name }}</strong>
               (induk sekarang: {{ moveRow.parent?.name || '— root —' }})
           </p>
           <div class="mb-2">
               <label class="form-label mb-1">Induk baru</label>
               <select class="form-control" v-model="moveParentId">
                   <option value="">— None (root) —</option>
                   <option v-for="org in moveParentOptions" :key="org.organization_id" :value="org.organization_id">
                       {{ org.name }}
                   </option>
               </select>
           </div>
       </div>
       <div class="text-end">
           <button class="btn btn-link-secondary" @click="showMove = false">Cancel</button>
           <button class="btn btn-primary" @click="submitMove">Move</button>
       </div>
   </BModal>
   ```

> Dropdown sengaja **hanya** membuang dirinya sendiri (kasus paling jelas). Kasus "induk baru = keturunan sendiri" dibiarkan tetap muncul di dropdown dan **ditangkap oleh 422** dari backend lalu pesannya ditampilkan di alert — ini sederhana dan tetap aman (lihat catatan Tahap 1).

**DoD Tahap 6**
- [ ] Move organisasi **leaf** (mis. sebuah District) ke Regional lain → setelah submit, kolom "Organization Parent" baris itu berubah jadi induk baru.
- [ ] Coba Move sebuah organisasi ke **dirinya sendiri**: tidak bisa dipilih karena tidak muncul di dropdown.
- [ ] Coba Move sebuah **Regional** ke salah satu **District di bawahnya** (keturunan) → muncul alert merah berisi pesan dari backend ("...cannot be moved under one of its own descendants."), data tidak berubah.
- [ ] Move sebuah organisasi ke opsi **"— None (root) —"** → organisasi jadi root, kolom parent menampilkan `-`.

---

## 7. Verifikasi akhir (manual, di browser)

- [ ] Login admin → buka `/organizations`.
- [ ] **Filter:** Type "Regional" → 12 baris; tambah Status "Active" → menyaring lagi; Reset → kembali 78.
- [ ] **Search + Filter** jalan bersamaan (filter dulu, search mempersempit hasil).
- [ ] **Move:** pindah satu District ke Regional lain (sukses), coba pindah ke keturunan sendiri (422 tertangani & pesan tampil), pindah ke root (parent jadi `-`).
- [ ] Tidak ada error di console browser (kecuali 422 yang memang sengaja diuji — itu ter-handle, bukan crash).
- [ ] Tidak ada perubahan di file selain [organizations.vue](frontend/src/views/pages/organizations.vue).

---

## Catatan untuk reviewer
- Filter murni client-side (konsisten dengan `users.vue`); jika dataset tumbuh sangat besar, nanti bisa dipindah ke server-side — **di luar lingkup issue ini**.
- Tombol Move memakai slot `#extra` agar `RowActions.vue` tidak perlu disentuh; bila kelak banyak halaman butuh aksi "move", pertimbangkan menambah entry `move` ke `actionMap` komponen — **juga di luar lingkup issue ini**.
