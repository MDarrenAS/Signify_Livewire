<p align="center">
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="https://livewire.laravel.com/"><img src="https://img.shields.io/badge/Livewire-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire"></a>
  <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
  <a href="https://supabase.com/"><img src="https://img.shields.io/badge/Supabase-3FCF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase"></a>
</p>

# Signify

Signify adalah aplikasi web pembelajaran Bahasa Isyarat Indonesia (BISINDO). Pengguna dapat mempelajari materi secara bertahap melalui modul, melihat kosakata pada kamus BISINDO, dan mengerjakan latihan serta kuis. Progres materi dan percobaan kuis disimpan agar aktivitas belajar dapat ditampilkan kembali pada dashboard.

Admin memiliki panel untuk mengelola modul, kosakata dan media kamus, serta paket kuis dan pertanyaannya. Autentikasi aplikasi menggunakan Laravel Fortify; Supabase digunakan untuk PostgreSQL dan penyimpanan media yang kompatibel dengan S3.

## Fitur

- Autentikasi dan pengaturan akun pengguna.
- Dashboard dengan ringkasan progres belajar, XP, streak, dan misi harian.
- Modul pembelajaran yang terdiri atas materi dan kuis.
- Kamus BISINDO dengan kategori, detail kosakata, serta media gambar atau video.
- Latihan kuis dengan penyimpanan skor dan percobaan pengguna.
- Panel admin untuk mengelola modul, kamus, media, dan kuis.
- Pembatasan akses panel administrasi berdasarkan peran pengguna.

## Teknologi

- PHP dan Laravel 13
- Livewire 4, Blade, dan Flux UI
- Tailwind CSS 4
- Vite Plus untuk pengembangan dan build aset frontend
- PostgreSQL melalui Supabase
- Supabase Storage melalui antarmuka S3 untuk media
- Pest dan PHPUnit untuk pengujian

## Persyaratan

- PHP 8.4.1 atau lebih baru untuk memenuhi persyaratan dependensi yang saat ini terpasang.
- Composer 2
- Node.js 22 dan npm

CI proyek menggunakan PHP 8.4 dan Node.js 22. `composer.json` mendeklarasikan PHP `^8.3`, tetapi versi dependensi yang terpasang saat ini meminta PHP minimal 8.4.1. Periksa kesesuaian runtime dan dependensi dengan `composer check-platform-reqs` sebelum menjalankan aplikasi atau tes.

## Menjalankan Secara Lokal

Dari direktori proyek, jalankan:

```bash
composer setup
composer dev
```

`composer setup` memasang dependensi, menyalin `.env.example` menjadi `.env` bila belum ada, membuat `APP_KEY`, menjalankan migrasi, memasang dependensi frontend, dan membangun aset. Konfigurasi awal `.env.example` menggunakan SQLite agar aplikasi dapat disiapkan secara lokal tanpa kredensial Supabase.

Setelah proses pengembangan berjalan, buka alamat lokal yang ditampilkan Artisan pada terminal. Jangan membagikan atau memasukkan file `.env`, kata sandi, token, maupun kredensial Supabase ke repository.

## Database dan Media

Untuk penggunaan lokal, SQLite dapat digunakan dengan konfigurasi bawaan. Untuk menghubungkan aplikasi ke PostgreSQL Supabase, atur variabel berikut pada `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_URL=
DB_SSLMODE=require
```

Untuk mengaktifkan penyimpanan media melalui Supabase Storage S3, isi kredensial dan endpoint dari pengaturan S3 Supabase pada `.env`:

```dotenv
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=bisindo-media
AWS_ENDPOINT=
```

Fitur unggah media menggunakan disk bernama `s3`; pengaturan `FILESYSTEM_DISK` bawaan tidak perlu diubah untuk fitur tersebut. Simpan kredensial hanya di environment server. Jangan mengeksposnya ke browser atau commit ke repository. Batas unggah aplikasi secara default 50 MB, sehingga `upload_max_filesize` dan `post_max_size` pada PHP perlu disetel agar tidak lebih kecil dari batas aplikasi.

Migrasi Laravel menyiapkan tabel aplikasi, termasuk tabel autentikasi dan progres. `docs/supabase_schema.sql` berisi skema PostgreSQL untuk fitur tambahan Supabase; file tersebut bukan pengganti migrasi Laravel. Autentikasi Signify menggunakan Laravel Fortify, bukan Supabase Auth, sehingga akses ke data pengguna harus tetap diperiksa oleh aplikasi Laravel.

## Pengujian dan Pemeriksaan

Tes menggunakan SQLite in-memory sesuai konfigurasi `phpunit.xml`, sehingga tidak memerlukan koneksi ke database Supabase.

```bash
php artisan test
composer lint
composer types:check
npm run build
```

Untuk menjalankan pemeriksaan CI proyek sekaligus:

```bash
composer ci:check
```

## Deployment

README ini tidak menetapkan penyedia hosting. Saat menyiapkan deployment, gunakan lingkungan yang dapat menjalankan aplikasi Laravel dan sediakan konfigurasi environment untuk `APP_KEY`, `APP_URL`, database, serta storage media. Pastikan juga session, cache, antrean, penyimpanan file, dan batas unggah sesuai dengan dukungan lingkungan hosting yang dipilih.

Jalankan migrasi sebagai langkah deployment yang direncanakan, bukan sebagai efek samping dari request web. Jangan menjalankan `composer setup` terhadap database produksi karena perintah tersebut menjalankan migrasi. Jika menggunakan Supabase, koneksi database dan akses storage harus disimpan di sisi server.

## Dokumentasi Proyek

- [Panduan coding](docs/CODING_GUIDELINES.md) — konvensi kontribusi dan pemeriksaan kode.
- [Skema Supabase](docs/supabase_schema.sql) — skema PostgreSQL untuk fitur tambahan Supabase.

## Anggota Tim

| Anggota | NIM | Peran | Tanggung Jawab |
| --- | --- | --- | --- |
| Alifah Putri Rhandiny | 245150401111047 | Frontend Developer | Membangun tampilan dan interaksi pengguna. |
| Aryanti Puspita Sari | 245150400111068 | UI/UX Designer dan Frontend Developer | Merancang desain dan alur aplikasi serta membangun tampilan dan interaksi pengguna. |
| Muhammad Darren Achilles Setiawan | 245150401111049 | Backend Developer | Membangun server, database, dan API. |
| A. Muh. Abduh Dzaky | 245150407111039 | Backend Developer | Membangun server, database, dan API. |
