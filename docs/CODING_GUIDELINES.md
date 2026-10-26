# Panduan Coding Signify

Panduan ini berlaku untuk perubahan pada Signify. Aplikasi menggunakan Laravel 13, Livewire 4, Blade, Tailwind CSS 4, dan Vite Plus. Ikuti pola yang sudah ada di repository dan utamakan perubahan yang jelas, aman, serta mudah diuji.

## Prinsip umum

- Baca implementasi terkait dan gunakan kembali komponen, helper, serta pola yang sudah ada sebelum menambahkan yang baru.
- Batasi perubahan pada kebutuhan pekerjaan dan perbarui dokumentasi yang terdampak.
- Gunakan nama yang deskriptif dan tipe yang eksplisit; jangan mengandalkan nilai dinamis atau asumsi tipe yang tidak tervalidasi.
- Jangan commit `.env`, kredensial, token, atau data sensitif.
- Jangan menambahkan dependency tanpa persetujuan.

## Laravel dan PHP

- Ikuti struktur yang ada di `app/`, `routes/`, `database/`, dan `tests/`. Gunakan Artisan untuk membuat file Laravel.
- Gunakan type hint parameter dan return type, serta kurung kurawal untuk semua struktur kontrol.
- Letakkan validasi dan otorisasi di sisi server. Jangan mempercayai nilai sensitif yang dikirim browser.
- Gunakan named route dan `route()` untuk membentuk URL.
- Hindari query berulang yang tidak perlu; gunakan relasi dan eager loading bila sesuai.
- Untuk perubahan skema Laravel, buat atau perbarui migration. Jangan menganggap SQL Supabase sebagai migration Laravel.
- Format PHP dengan `vendor/bin/pint --dirty --format agent`.

## Livewire dan antarmuka

- Bangun interaksi dinamis dengan Livewire dan pertahankan state penting di server. Gunakan Alpine.js atau JavaScript hanya jika diperlukan oleh pola UI yang ada.
- Validasi input dan lakukan otorisasi di setiap aksi Livewire yang mengubah atau membaca data terbatas.
- Gunakan Blade untuk tampilan dan manfaatkan komponen Flux yang sudah tersedia sebelum membuat komponen baru.
- Ikuti gaya dan tema yang sudah ada; styling proyek menggunakan Tailwind CSS 4 di `resources/css/app.css`.
- Tampilkan status loading, validasi, dan kegagalan secara jelas. Jangan menyembunyikan kegagalan dengan fallback yang terlihat seperti sukses.
- Tempatkan perubahan JavaScript di `resources/js/` dan CSS di `resources/css/`; hindari menambahkan framework frontend lain tanpa persetujuan.

## Database dan Supabase

- `docs/supabase_schema.sql` adalah skema PostgreSQL yang ditujukan untuk Supabase, bukan migration Laravel. Skema itu menggunakan objek Supabase seperti `auth.users`, `auth.uid()`, serta Row Level Security (RLS).
- Saat mengubah tabel, relasi, constraint, trigger, view, atau policy Supabase, perbarui skema tersebut agar tetap konsisten dan tinjau dampaknya terhadap RLS.
- Batasi akses sesuai peran dan kepemilikan data. Jangan menonaktifkan RLS atau melonggarkan policy hanya untuk mempermudah implementasi.
- Jangan mengekspos kunci service-role atau secret database ke browser maupun repository.
- Nilai sensitif seperti kunci jawaban dan hasil penilaian harus diproses di sisi server, bukan dipercayakan kepada klien.
- Aplikasi saat ini menggunakan autentikasi Laravel/Fortify. Jangan menganggap autentikasi Supabase pada skema sebagai integrasi yang sudah aktif; perubahan autentikasi harus mengikuti implementasi aplikasi yang ada.

## Pengujian dan pemeriksaan

- Tambahkan atau perbarui tes Pest untuk perubahan perilaku yang bermakna, terutama validasi, otorisasi, dan akses data.
- Jalankan tes yang relevan dengan `php artisan test --compact <path-tes>` atau `php artisan test --compact --filter=<nama-tes>`.
- Jalankan pemeriksaan yang relevan dengan perubahan:

```bash
composer lint
composer types:check
npm run build
php artisan test --compact
```

## Commit message

Gunakan format:

```text
<type>: <ringkasan singkat dalam bentuk perintah>
```

Gunakan huruf kecil untuk `<type>` dan ringkasan spesifik tanpa titik di akhir:

- `feat:` — menambahkan fitur.
- `fix:` — memperbaiki bug.
- `docs:` — mengubah dokumentasi.
- `style:` — perubahan format atau tampilan tanpa perubahan perilaku.
- `refactor:` — merapikan struktur tanpa mengubah perilaku.
- `test:` — menambah atau memperbarui tes.
- `chore:` — pemeliharaan atau konfigurasi pendukung.

Contoh: `docs: perbarui panduan coding Signify`.
