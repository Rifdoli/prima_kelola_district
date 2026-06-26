# Prima Kelola District

Aplikasi dengan backend **Laravel (API-only)** dan frontend **Vue 3 (template LightAble)**, database **PostgreSQL**.

```
prima_kelola_district/
├── backend/   ← Laravel API
└── frontend/  ← Vue 3 (LightAble template)
```

Lihat [issue #2](https://github.com/Rifdoli/prima_kelola_district/issues/2) untuk roadmap pengerjaan lengkap.

## Prasyarat

- PHP 8.2+ & Composer
- Node.js & npm
- PostgreSQL (berjalan secara lokal)

## Menjalankan Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Buat database PostgreSQL kosong, lalu sesuaikan kredensial di `.env` (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

```bash
php artisan migrate --seed
php artisan serve
```

Backend berjalan di `http://localhost:8000`. Seeder membuat role `admin`/`user` dan satu user admin:

- Email: `test@example.com`
- Password: `password`

## Menjalankan Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run serve
```

Frontend berjalan di `http://localhost:8080`. Pastikan `VUE_APP_API_URL` di `.env` mengarah ke URL backend (default: `http://localhost:8000/api`).

## Alur Coba Cepat (Smoke Test)

1. Buka `http://localhost:8080/login-v1`, login dengan `test@example.com` / `password`.
2. Setelah login, buka `http://localhost:8080/roles` — kelola data role (modul contoh CRUD).
3. Logout lewat halaman/aksi logout pada template.

Alur ini juga sudah diverifikasi langsung lewat API (register → login → akses modul → logout) tanpa error.

## Dokumentasi API

Lihat [backend/README.md](backend/README.md) untuk daftar endpoint dan contoh request/response.
