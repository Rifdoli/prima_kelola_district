# Prima Kelola District — Backend (Laravel API)

Backend API-only (Laravel + Sanctum + PostgreSQL) untuk project Prima Kelola District. Lihat [README di root](../README.md) untuk panduan setup lengkap (BE + FE).

## Setup Singkat

```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_* di .env ke kredensial PostgreSQL lokal Anda
php artisan migrate --seed
php artisan serve
```

Seeder membuat 5 role berjenjang (SUPERADMIN s/d ADMIN DISTRICT, lihat Bagian Role di bawah), dan satu user SUPERADMIN: `test@example.com` / `password`.

## Endpoint API

Semua response berbentuk `{ "data": ..., "message": "..." }`.

### Auth (publik)

| Method | Endpoint        | Body                                                    |
| ------ | --------------- | -------------------------------------------------------- |
| POST   | `/api/register` | `name`, `username`, `email`, `password`, `password_confirmation`, `phone_number?` |
| POST   | `/api/login`    | `email`, `password`                                       |

Validasi `/api/register`: `password` minimal 8 karakter dan harus cocok dengan `password_confirmation`; `email` dan `username` harus unik.

Contoh response (`/api/login`):

```json
{
  "data": {
    "user": {
      "user_id": 1,
      "uuid": "0284625c-2478-4baf-918c-57cef81af067",
      "name": "Test User",
      "username": "testuser",
      "email": "test@example.com",
      "role_id": 1,
      "is_active": true,
      "last_login_at": "2026-06-26T20:17:19.000000Z"
    },
    "token": "1|xxxxxxxx"
  },
  "message": "Login successful."
}
```

Catatan: `user_id` adalah PK internal (pakai ini untuk relasi/FK di DB). `uuid` disiapkan sebagai identifier publik untuk URL/response API ke depannya. Field `password`, `remember_token`, dan `device_token` selalu disembunyikan dari response.

Gunakan `token` di atas sebagai header `Authorization: Bearer <token>` untuk semua endpoint di bawah ini.

### Auth (perlu login)

| Method | Endpoint      | Keterangan              |
| ------ | ------------- | ------------------------ |
| GET    | `/api/user`   | Data user yang sedang login |
| POST   | `/api/logout` | Logout, revoke token     |

### User & Role Management (perlu login + role SUPERADMIN)

| Method | Endpoint          | Keterangan      |
| ------ | ----------------- | --------------- |
| GET    | `/api/roles`      | List role       |
| POST   | `/api/roles`      | Buat role baru (`name`, `sname`, `description?`, `is_active?`) |
| GET    | `/api/roles/{id}` | Detail role     |
| PUT    | `/api/roles/{id}` | Update role (`name?`, `description?`, `is_active?`) |
| DELETE | `/api/roles/{id}` | Hapus role      |
| GET    | `/api/users`      | List user (dengan role) |
| POST   | `/api/users`      | Buat user baru (`name`, `username`, `email`, `password`, `phone_number?`, `role_id?`) |
| GET    | `/api/users/{id}` | Detail user     |
| PUT    | `/api/users/{id}` | Update user (`name?`, `username?`, `email?`, `phone_number?`, `is_active?`, `role_id?`) |
| DELETE | `/api/users/{id}` | Hapus user      |

Setiap role punya `sname` — identifier stabil untuk otorisasi (mis. `admin_sup`), **berbeda dengan `name`** yang hanya label tampilan (mis. `SUPERADMIN`). `sname` diisi sekali saat role dibuat (lewat `POST /api/roles`) dan **tidak bisa diubah** lagi lewat `PUT`. Middleware `EnsureUserIsAdmin` memeriksa `sname === 'admin_sup'` — jadi mengganti `name` (kapitalisasi, terjemahan, dll) tidak akan mengunci akses admin.

Role yang di-seed secara default:

| name | sname | description |
| --- | --- | --- |
| SUPERADMIN | `admin_sup` | User Pengelola Utama Aplikasi |
| ADMIN NASIONAL | `admin_nas` | User Utama Nasional |
| ADMIN AREA | `admin_are` | User Utama Area |
| ADMIN REGIONAL | `admin_reg` | User Utama Regional |
| ADMIN DISTRICT | `admin_dis` | User Utama District |

Hanya role dengan `sname = admin_sup` (SUPERADMIN) yang punya akses ke endpoint-endpoint di atas. Role lain & user tanpa role akan menerima `403 Forbidden`.

## Response Error

- **401 Unauthorized** — token tidak ada/tidak valid. Format bawaan Laravel: `{ "message": "Unauthenticated." }`.
- **403 Forbidden** — token valid tapi role tidak diizinkan (mis. bukan `admin`). Mengikuti pola sukses: `{ "data": null, "message": "Forbidden. Admin access required." }`.
- **422 Unprocessable Entity** — validasi gagal. Format bawaan Laravel, dengan field tambahan `errors` berisi daftar pesan per field:

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

## Menjalankan Test

```bash
php artisan test
```
