<p align="center">
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="https://livewire.laravel.com/"><img src="https://img.shields.io/badge/Livewire-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire"></a>
  <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
  <a href="https://supabase.com/"><img src="https://img.shields.io/badge/Supabase-3FCF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase"></a>
</p>

# Signify

Signify adalah website yang dirancang untuk membantu pembelajaran Sistem Isyarat Bahasa Indonesia (SIBINDO). Repository ini dibangun di atas Laravel dan starter kit Livewire. Saat ini, aplikasi menyediakan fondasi autentikasi, dashboard, dan pengaturan akun; kamus isyarat, materi, kuis, serta pelacakan progres masih merupakan ruang lingkup produk yang direncanakan.

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

File `.env.example` menyediakan konfigurasi SQLite untuk penggunaan lokal tanpa kredensial database, dan pengujian juga diarahkan ke SQLite melalui `phpunit.xml`. Sebelum menjalankan `composer setup`, pastikan koneksi database yang aktif menunjuk ke database pengembangan yang benar. Perintah tersebut menjalankan migrasi pada database aktif.

## Pemeriksaan kode

```bash
npm run build
composer lint
composer types:check
php artisan test
```
