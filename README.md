<p align="center">
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="https://livewire.laravel.com/"><img src="https://img.shields.io/badge/Livewire-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire"></a>
  <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
  <a href="https://supabase.com/"><img src="https://img.shields.io/badge/Supabase-3FCF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase"></a>
</p>

# Signify

Signify adalah website yang dirancang untuk membantu pembelajaran Sistem Isyarat Bahasa Indonesia (SIBINDO). Repository ini dibangun di atas Laravel dan starter kit Livewire. Saat ini, aplikasi menyediakan fondasi autentikasi, dashboard, dan pengaturan akun; kamus isyarat, materi, kuis, serta pelacakan progres masih merupakan ruang lingkup produk yang direncanakan.

## Dokumentasi proyek

- [Panduan coding](docs/CODING_GUIDELINES.md) — konvensi dan pemeriksaan untuk kontribusi ke repository.
- [Skema Supabase](docs/supabase_schema.sql) — skema PostgreSQL, relasi, dan kebijakan Row Level Security (RLS) untuk Supabase.

## Teknologi

- PHP 8.3+ dan Laravel 13
- Livewire 4 dan Flux UI
- Blade, Tailwind CSS 4, dan JavaScript
- Vite Plus untuk pengembangan dan bundling aset frontend
- PostgreSQL melalui Supabase sebagai database aplikasi

## Anggota tim

| Anggota | NIM | Peran | Tanggung Jawab |
| --- | --- | --- | --- |
| Alifah Putri Rhandiny | 245150401111047 | Frontend Developer | Membangun tampilan dan interaksi pengguna. |
| Aryanti Puspita Sari | 245150400111068 | UI/UX Designer dan Frontend Developer | Merancang desain dan alur aplikasi serta membangun tampilan dan interaksi pengguna. |
| Muhammad Darren Achilles Setiawan | 245150401111049 | Backend Developer | Membangun server, database, dan API. |
| A. Muh. Abduh Dzaky | 245150407111039 | Backend Developer | Membangun server, database, dan API. |

## Persiapan lokal

Pastikan PHP 8.3+, Composer, Node.js, dan npm tersedia. Dari direktori proyek, jalankan:

```bash
composer setup
composer dev
```

`composer setup` memasang dependensi PHP dan JavaScript, menyiapkan `.env`, membuat application key, menjalankan migrasi, dan membangun aset frontend. `composer dev` menjalankan lingkungan pengembangan Laravel.

## Database

Aplikasi menggunakan PostgreSQL melalui Supabase. Koneksi Laravel menggunakan driver `pgsql` dan dikonfigurasi melalui variabel environment, seperti `DB_CONNECTION` dan `DB_URL`. Jangan membagikan atau memasukkan file `.env` maupun kredensial Supabase ke commit.

File `.env.example` menyediakan konfigurasi SQLite untuk penggunaan lokal tanpa kredensial database, dan pengujian juga diarahkan ke SQLite melalui `phpunit.xml`. Migration Laravel menjadi sumber skema untuk autentikasi Fortify, termasuk tabel `users`, passkeys, two-factor authentication, role, dan status aktif. `docs/supabase_schema.sql` hanya membuat tabel fitur tambahan dan policy Data API; jalankan migration Laravel terlebih dahulu pada database Supabase sebelum menjalankan SQL tersebut.

## Deployment ke Vercel

Konfigurasi saat ini menggunakan [`vercel-php@0.9.0`](https://github.com/vercel-community/php), runtime PHP komunitas untuk Vercel (PHP 8.5), bukan runtime PHP resmi Vercel. Vercel mendukung runtime kustom, tetapi deployment Laravel ini tetap perlu diverifikasi pada project Vercel sebenarnya. `vercel.json` mengatur build aset, fungsi PHP, routing, region, serta direktori sementara yang diperlukan Laravel. Region `bom1` harus disesuaikan bila region database Supabase berada di lokasi lain; tempatkan fungsi sedekat mungkin dengan database.

Atur secret di **Vercel Project Settings → Environment Variables**, bukan di repository:

- `APP_KEY`: application key Laravel yang stabil untuk semua deployment environment terkait.
- `APP_URL`: URL HTTPS deployment.
- `DB_URL`: connection string [**Transaction pooler**](https://supabase.com/docs/guides/database/connecting-to-postgres) dari tombol **Connect** di Supabase. Gunakan host, username, dan password persis seperti yang diberikan Supabase; mode ini umumnya menggunakan port `6543`. Encode karakter khusus pada password sesuai format URL.

`DB_CONNECTION=pgsql`, `DB_SSLMODE=require`, `APP_ENV=production`, dan `APP_DEBUG=false` sudah menjadi konfigurasi deployment. Filesystem Vercel hanya dapat ditulis sementara di `/tmp`; file hasil upload harus menggunakan storage persisten eksternal. Session cookie, cache array, dan queue sync yang dikonfigurasi saat ini juga tidak menyediakan state persisten atau worker antrean.

**Jangan jalankan `composer setup` di production:** perintah itu menjalankan migrasi pada database aktif. Jalankan migration Laravel dan SQL fitur Supabase sebagai langkah deployment terpisah, bukan dari request web. Autentikasi aplikasi menggunakan Laravel/Fortify, bukan Supabase Auth; karena itu, policy Data API tidak memberi identitas Fortify kepada Postgres. Koneksi database Laravel harus tetap server-side, dan setiap aksi yang mengakses data milik pengguna harus diotorisasi di Laravel.

## Pemeriksaan kode

```bash
npm run build
composer lint
composer types:check
php artisan test
```
