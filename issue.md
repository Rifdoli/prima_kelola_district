# Issue: Halaman Organization Management (Frontend CRUD)

> **Untuk implementor (junior / AI):** Kerjakan **berurutan** dari Tahap 1. Semua perubahan ada di folder `frontend/` — **tidak ada perubahan backend** (API-nya sudah jadi). **Tiru pola dari file referensi** yang path-nya disebut; jangan mengarang pola baru. Setiap tahap punya checklist **Definition of Done (DoD)**. Jika ragu, **berhenti dan tanya**.

---

## 0. Konteks & Ruang Lingkup

Backend modul Organizations **sudah selesai dan ter-seed** (78 organisasi: 1 National, 4 Area, 12 Regional, 61 District). Yang belum ada adalah **halaman frontend-nya** — route `/organizations` saat ini masih menampilkan halaman placeholder. Tugas issue ini: bikin halaman CRUD-nya memakai struktur tabel admin yang sudah dipakai di halaman Users & Roles.

### File REFERENSI (tiru polanya — JANGAN diubah)
- [frontend/src/views/pages/users.vue](frontend/src/views/pages/users.vue) — **template utama yang ditiru**: pakai `AdminDataTable` + `RowActions` + `BModal` untuk Add/Edit/View/Filter, fetch via `api`.
- [frontend/src/views/pages/roles.vue](frontend/src/views/pages/roles.vue) — versi lebih sederhana (field lebih sedikit), berguna sebagai pembanding.
- [frontend/src/components/common/AdminDataTable.vue](frontend/src/components/common/AdminDataTable.vue) — komponen tabel (search, pagination, sort, slot kolom). **Pelajari cara kerja slot kolomnya** (lihat Tahap 3).
- [frontend/src/components/common/RowActions.vue](frontend/src/components/common/RowActions.vue) — tombol aksi view/edit/delete (emit `@view`/`@edit`/`@delete`).
- [frontend/src/services/api.js](frontend/src/services/api.js) — axios instance (base URL + token otomatis). Pakai ini untuk semua request.

### Yang TIDAK boleh dikerjakan
- Tidak mengubah backend (controller/model/migration/route).
- Tidak mengubah file referensi di atas.
- Tidak menampilkan kolom `id` atau `uuid` di tabel (prinsip yang sama dengan halaman Users/Roles — nomor urut pakai `#` dari `AdminDataTable`).

---

## 1. Kontrak API (sudah tersedia — JANGAN bikin endpoint baru)

Semua endpoint butuh login admin (token sudah otomatis dilampirkan oleh [api.js](frontend/src/services/api.js)). Bentuk response selalu `{ data, message }`.

| Aksi | Request | Catatan |
|---|---|---|
| List organisasi | `GET /organizations` | tiap item sudah membawa relasi `type` (objek `organization_types`) dan `parent` (objek organisasi induk, bisa `null`). |
| List tipe | `GET /organization-types` | untuk isi dropdown **Organization Type**. Tiap item: `{ organization_type_id, name, level, ... }`. |
| Tambah | `POST /organizations` | body: `name` (wajib), `sname` (wajib, unik), `organization_type_id` (wajib), `parent_organization_id` (boleh `null` = root), opsional `timezone`, `is_active`, `notes`. |
| Edit | `PUT /organizations/{id}` | body: `name`, `sname`, `organization_type_id`, `is_active`, `timezone`, `notes`. **`parent_organization_id` TIDAK diterima di sini** (pindah induk pakai endpoint `move`, lihat Tahap 6 opsional). |
| Hapus | `DELETE /organizations/{id}` | jika organisasi punya keturunan → **422**. Untuk hapus beserta seluruh sub-pohonnya, kirim query `?cascade=true`. Lihat Tahap 5. |

> **Beda penting dengan Roles:** di Organizations, `sname` **boleh diubah** saat edit (backend menerimanya). Di Roles `sname` immutable — jangan terbawa pola itu.

**DoD Tahap 1**
- [ ] Sudah paham 5 endpoint di atas (tidak ada yang perlu dibuat; semua sudah ada).

---

## 2. Buat file halaman & sambungkan route

1. Buat file baru `frontend/src/views/pages/organizations.vue` (mulai dengan menyalin struktur [users.vue](frontend/src/views/pages/users.vue), lalu sesuaikan).
2. Di [frontend/src/router/routes.js](frontend/src/router/routes.js), cari entri route `path: "/organizations"` — saat ini `component`-nya `placeholder.vue`. Ganti menjadi:
   ```js
   component: () => import("../views/pages/organizations.vue"),
   ```
   Biarkan `meta: { title: "Organization Management", group: "Administration" }` apa adanya (breadcrumb sudah benar).
3. Link sidebar **sudah ada** (menu Administration → Organization Management) — tidak perlu menyentuh sidebar.

**DoD Tahap 2**
- [ ] Buka `/organizations` di browser → halaman baru muncul (boleh masih kosong), bukan lagi placeholder "dalam pengembangan".

---

## 3. Tabel: kolom & cara mengisi sel relasi

`pageheader`: `title="Organization Management"`, `pageTitle="Administration"`.

Definisikan `columns` di `data()`:
```js
columns: [
    { key: "name", label: "Name", sortable: true },
    { key: "sname", label: "Short Name", sortable: true },
    { key: "type.name", label: "Organization Type", sortable: true },
    { key: "parent.name", label: "Organization Parent", sortable: true },
],
```
Kolom `#` dan `Actions` **otomatis** disediakan `AdminDataTable` — jangan dimasukkan ke `columns`.

### GOTCHA slot kolom relasi (WAJIB dibaca)
`AdminDataTable` mengubah titik (`.`) pada `key` menjadi strip (`-`) untuk nama slot (lihat method `slotName` di [AdminDataTable.vue](frontend/src/components/common/AdminDataTable.vue)). Jadi:
- `key: "type.name"` → slotnya **`#cell-type-name`**
- `key: "parent.name"` → slotnya **`#cell-parent-name`**

Sediakan slot ini supaya nilai relasi tampil benar dan aman saat `parent` bernilai `null`:
```html
<template #cell-type-name="{ row }">{{ row.type?.name }}</template>
<template #cell-parent-name="{ row }">{{ row.parent?.name || '-' }}</template>
```

Set search lintas kolom (termasuk relasi) di `AdminDataTable`:
```html
<AdminDataTable title="Organization Table" :columns="columns" :rows="organizations" :loading="loading"
    :search-keys="['name', 'sname', 'type.name', 'parent.name']">
```

**DoD Tahap 3**
- [ ] Tabel menampilkan 78 organisasi dengan kolom `#`, Name, Short Name, Organization Type, Organization Parent, Actions.
- [ ] Organisasi root (TELKOM INFRASTUKTUR INDONESIA) menampilkan parent `-` (bukan error/blank).
- [ ] Ketik "regional" / "jateng" di search → tabel terfilter; sorting kolom berfungsi.

---

## 4. Fetch data & Add/Edit/View (modal)

Tiru langsung pola `users.vue`:
- `mounted()`: panggil `fetchOrganizations()` dan `fetchTypes()`.
- `fetchOrganizations()` → `GET /organizations` → simpan ke `this.organizations`.
- `fetchTypes()` → `GET /organization-types` → simpan ke `this.types` (untuk dropdown).
- `form` di `data()`: `{ organization_id: null, name: "", sname: "", organization_type_id: "", parent_organization_id: "", is_active: true }`.

### Modal Add (`POST /organizations`)
Field form:
- **Name** → `form.name`
- **Short Name** → `form.sname`
- **Organization Type** → `<select v-model="form.organization_type_id">` di-loop dari `this.types` (`:value="t.organization_type_id"`, tampilkan `t.name`).
- **Organization Parent** → `<select v-model="form.parent_organization_id">` di-loop dari `this.organizations`. Sediakan opsi pertama `<option value="">— None (root) —</option>`. Kirim `null` jika kosong.
- **Active** → checkbox `form.is_active`.

Validasi minimal di frontend: `name`, `sname`, `organization_type_id` wajib. Setelah sukses, tutup modal & `fetchOrganizations()`.

### Modal Edit (`PUT /organizations/{id}`)
Sama dengan Add **kecuali**: **jangan tampilkan dropdown Parent** (backend tidak menerima `parent_organization_id` di update; ganti induk = fitur terpisah Tahap 6). `sname` boleh diedit. Kirim: `name`, `sname`, `organization_type_id`, `is_active`.

### Modal View (read-only)
Tampilkan: Name, Short Name, Organization Type (`viewRow.type?.name`), Organization Parent (`viewRow.parent?.name || '-'`), Active.

Pasang `RowActions` di slot `#actions` persis seperti users.vue:
```html
<template #actions="{ row }">
    <RowActions :row="row" @view="openView" @edit="openEdit" @delete="deleteOrganization" />
</template>
```

**DoD Tahap 4**
- [ ] Add organisasi baru (mis. District di bawah salah satu Regional) → muncul di tabel dengan parent benar.
- [ ] Edit name/short name/type/active → tersimpan & ter-refresh.
- [ ] View menampilkan detail lengkap.

---

## 5. Hapus dengan penanganan cascade (PENTING)

`DELETE /organizations/{id}` akan **gagal 422** bila organisasi punya keturunan. Tangani begini di `deleteOrganization(org)`:

1. `confirm("Delete organization \"<name>\"?")`. Jika batal, stop.
2. Coba `DELETE /organizations/{id}`.
3. Jika berhasil → `fetchOrganizations()`.
4. Jika error **status 422** (artinya punya keturunan) → tampilkan `confirm` kedua memakai pesan dari `error.response.data.message` (mis. "This organization has N descendant organization(s)…"). Jika user setuju → ulangi request dengan query cascade: `DELETE /organizations/{id}?cascade=true`, lalu `fetchOrganizations()`.

Contoh inti:
```js
async deleteOrganization(org) {
    if (!confirm(`Delete organization "${org.name}"?`)) return;
    try {
        await api.delete(`/organizations/${org.organization_id}`);
        this.fetchOrganizations();
    } catch (error) {
        if (error.response?.status === 422) {
            const msg = error.response.data.message + "\n\nLanjut hapus beserta seluruh sub-organisasinya?";
            if (confirm(msg)) {
                await api.delete(`/organizations/${org.organization_id}?cascade=true`);
                this.fetchOrganizations();
            }
        } else {
            this.error = error.response?.data?.message || 'Failed to delete organization.';
        }
    }
}
```

**DoD Tahap 5**
- [ ] Hapus organisasi **leaf** (District tanpa anak) → langsung terhapus.
- [ ] Hapus organisasi yang **punya anak** (mis. sebuah Regional) → muncul konfirmasi kedua; jika disetujui, organisasi + seluruh keturunannya terhapus dan tabel ter-update.

---

## 6. (OPSIONAL) Pindah induk via endpoint `move`

Mengganti induk organisasi tidak lewat Edit, tapi lewat `POST /organizations/{id}/move` dengan body `{ "parent_organization_id": <id|null> }`. Backend menolak (422) bila tujuan = dirinya sendiri atau keturunannya sendiri.

Jika ada waktu, tambahkan tombol/aksi "Move" yang membuka modal berisi dropdown parent baru, lalu panggil endpoint tersebut. **Opsional** — sepakati dulu sebelum mengerjakan; tidak wajib untuk menutup issue ini.

---

## 7. (OPSIONAL) Filter modal

Seperti di `users.vue`, boleh tambahkan tombol **Filter** + modal untuk menyaring berdasar **Organization Type** dan/atau **Active**. Polanya sama persis (data `filter`, computed `filteredOrganizations`, modal Filter, method `resetFilter`). **Opsional.**

---

## 8. Verifikasi akhir (manual)

- [ ] `/organizations` menampilkan 78 organisasi dengan 4 kolom data + `#` + Actions.
- [ ] Search, sort, pagination jalan.
- [ ] Add / Edit / View / Delete (termasuk skenario cascade) berfungsi end-to-end.
- [ ] Breadcrumb: `Home > Administration > Organization Management`.
- [ ] Tidak ada `id`/`uuid` yang bocor di tampilan tabel.

---

## Pertanyaan terbuka (konfirmasi bila relevan)
1. Perlu kolom **Active/Status** di tabel? Spesifikasi awal hanya 4 kolom data (Name, Short Name, Type, Parent). Default: ikuti spesifikasi (Active hanya di form/modal, tidak di tabel).
2. Fitur **Move** (Tahap 6) dan **Filter** (Tahap 7) masuk issue ini atau dipisah? Default: opsional / dipisah.
