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

Seeder membuat role `admin`/`user`, dan satu user admin: `test@example.com` / `password`.

## Endpoint API

Semua response berbentuk `{ "data": ..., "message": "..." }`.

### Auth (publik)

| Method | Endpoint        | Body                                                    |
| ------ | --------------- | -------------------------------------------------------- |
| POST   | `/api/register` | `name`, `email`, `password`, `password_confirmation`     |
| POST   | `/api/login`    | `email`, `password`                                       |

Validasi `/api/register`: `password` minimal 8 karakter dan harus cocok dengan `password_confirmation`; `email` harus unik.

Contoh response (`/api/login`):

```json
{
  "data": {
    "user": { "id": 1, "name": "Test User", "email": "test@example.com", "role_id": 1 },
    "token": "1|xxxxxxxx"
  },
  "message": "Login successful."
}
```

Gunakan `token` di atas sebagai header `Authorization: Bearer <token>` untuk semua endpoint di bawah ini.

### Auth (perlu login)

| Method | Endpoint      | Keterangan              |
| ------ | ------------- | ------------------------ |
| GET    | `/api/user`   | Data user yang sedang login |
| POST   | `/api/logout` | Logout, revoke token     |

### User & Role Management (perlu login + role `admin`)

| Method | Endpoint          | Keterangan      |
| ------ | ----------------- | --------------- |
| GET    | `/api/roles`      | List role       |
| POST   | `/api/roles`      | Buat role baru (`name`) |
| GET    | `/api/roles/{id}` | Detail role     |
| PUT    | `/api/roles/{id}` | Update role (`name`) |
| DELETE | `/api/roles/{id}` | Hapus role      |
| GET    | `/api/users`      | List user (dengan role) |
| POST   | `/api/users`      | Buat user baru (`name`, `email`, `password`, `role_id?`) |
| GET    | `/api/users/{id}` | Detail user     |
| PUT    | `/api/users/{id}` | Update user (`name?`, `email?`, `role_id?`) |
| DELETE | `/api/users/{id}` | Hapus user      |

User tanpa role `admin` akan menerima `403 Forbidden` pada endpoint-endpoint di atas.

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
