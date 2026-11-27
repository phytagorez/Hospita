# Hospita

Hospita adalah aplikasi web untuk reservasi dan administrasi klinik. Pasien dapat mencari jadwal dokter, membuat janji, dan menyimpan tiket antrean. Petugas dapat mengelola jadwal praktik, memproses kunjungan, mencatat pembayaran, serta mengunduh laporan.

## Fitur

| Pengguna | Fitur                                                                                                                                                                                                                      |
| -------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Pasien   | Login, pengisian profil, pencarian jadwal dokter, reservasi sesuai kuota, tiket QR, riwayat kunjungan, pembatalan sebelum sesi dan sebelum check-in, serta akses nota.                                                     |
| Admin    | Dashboard kunjungan, check-in dengan kode tiket, pendaftaran walk-in, pengelolaan antrean, pengaturan poli dan jadwal dokter, pencarian pasien, pencatatan pembayaran tunai atau QRIS, serta ekspor laporan PDF dan Excel. |

Alur kunjungan dimulai dari pemilihan jadwal dan penerbitan tiket. Saat pasien tiba, petugas melakukan check-in, mengelola antrean hingga pemeriksaan selesai, lalu mencatat pembayaran di kasir.

## Teknologi

| Bagian            | Teknologi                                                           |
| ----------------- | ------------------------------------------------------------------- |
| Backend           | Laravel 13 dan PHP                                                  |
| Frontend          | Inertia.js 3, Vue 3, TypeScript, dan Tailwind CSS 4                 |
| Build dan routing | Vite+ dan Laravel Wayfinder                                         |
| Basis data        | MySQL untuk konfigurasi bawaan dan SQLite in-memory untuk pengujian |
| Kualitas kode     | Pest, Laravel Pint, Larastan/PHPStan, dan pemeriksaan TypeScript    |

## Instalasi lokal

Siapkan Git, **PHP 8.5**, Composer 2, **Node.js 22.18+ pada seri 22 atau 24.11+ pada seri 24**, npm, dan MySQL. Aktifkan ekstensi PHP `pdo_mysql`, `pdo_sqlite`, dan `zip` untuk koneksi database, pengujian, serta ekspor Excel. Di Windows dengan Laragon, jalankan MySQL dan pastikan versi PHP CLI sesuai saat menjalankan `php -v`.

1. Klon repositori dan pasang dependensi dari lockfile.

    ```bash
    git clone https://github.com/phytagorez/Hospita.git
    cd Hospita
    composer install
    npm ci
    ```

2. Salin konfigurasi awal. Gunakan perintah yang sesuai dengan terminal.

    **PowerShell**

    ```powershell
    Copy-Item .env.example .env
    ```

    **Bash**

    ```bash
    cp .env.example .env
    ```

3. Buat database kosong `rumah_sakit` di MySQL, lalu sesuaikan nilai berikut di `.env` dengan koneksi lokalmu.

    ```dotenv
    APP_URL=http://127.0.0.1:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=rumah_sakit
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4. Buat kunci aplikasi dan jalankan migrasi.

    ```bash
    php artisan key:generate
    php artisan migrate
    ```

5. Jalankan server pengembangan.

    ```bash
    composer dev
    ```

    Buka [Hospita di server lokal](http://127.0.0.1:8000). Perintah ini menjalankan Laravel, Vite, dan queue listener. Jika memakai virtual host Laragon, sesuaikan `APP_URL` dengan alamat yang dipakai untuk membuka aplikasi.

## Data demo dan akun

Untuk mencoba alur dari reservasi sampai kasir, isi `CLINIC_ADMIN_PASSWORD` dan `CLINIC_PATIENT_PASSWORD` di `.env` dengan kata sandi pilihanmu. Jalankan seeder pada database lokal.

```bash
php artisan db:seed
```

| Akun        | Email                                               | Kata sandi                      |
| ----------- | --------------------------------------------------- | ------------------------------- |
| Admin       | `admin@klinik.test` atau nilai `CLINIC_ADMIN_EMAIL` | Nilai `CLINIC_ADMIN_PASSWORD`   |
| Pasien demo | `pasien@klinik.test`                                | Nilai `CLINIC_PATIENT_PASSWORD` |

Seeder menyiapkan contoh poli, dokter, dan jadwal praktik pada lingkungan lokal. Akses pasien demo dengan email dan kata sandi tersedia saat `APP_ENV=local`. Seeder mempertahankan kata sandi akun yang sudah pernah dibuat. Simpan konfigurasi dan kredensial hanya di `.env`.

### Login dengan Google

Untuk mengaktifkan login Google, isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di `.env`. Daftarkan alamat callback yang terdiri dari URL aplikasi yang digunakan di browser diikuti `/auth/google/callback`. Contoh untuk konfigurasi lokal di atas adalah `http://127.0.0.1:8000/auth/google/callback`. Setelah mengubah konfigurasi, jalankan `php artisan config:clear` jika konfigurasi sebelumnya tersimpan di cache.

## Pemeriksaan kode dan build

Jalankan pemeriksaan dari root proyek setelah dependensi terpasang.

| Perintah                     | Kegunaan                                                                            |
| ---------------------------- | ----------------------------------------------------------------------------------- |
| `npm run check`              | Memeriksa format dan lint frontend.                                                 |
| `npm run types:check`        | Memeriksa tipe TypeScript dan komponen Vue.                                         |
| `composer test`              | Menjalankan pemeriksaan Pint, analisis PHPStan, dan tes Laravel.                    |
| `php artisan test --compact` | Menjalankan tes Laravel secara langsung.                                            |
| `composer ci:check`          | Menjalankan rangkaian pemeriksaan frontend dan backend.                             |
| `npm run build`              | Mengompilasi aset produksi ke `public/build/` dan menghasilkan tipe rute Wayfinder. |

Jika server menampilkan pesan **Vite manifest not found**, jalankan `composer dev` atau buat build dengan `npm run build`. Jika pemeriksaan TypeScript gagal karena impor rute Wayfinder belum tersedia, jalankan build terlebih dahulu.

## Struktur proyek

| Lokasi                           | Isi                                                                   |
| -------------------------------- | --------------------------------------------------------------------- |
| [app/](app/)                     | Controller, model, validasi, logika reservasi, dan pembuatan laporan. |
| [resources/js/](resources/js/)   | Halaman Inertia, komponen Vue, dan tipe data frontend.                |
| [resources/css/](resources/css/) | Gaya global dan komponen visual bersama.                              |
| [routes/web.php](routes/web.php) | Rute publik, pasien, dan admin.                                       |
| [database/](database/)           | Migrasi, factory, dan seeder.                                         |
| [tests/](tests/)                 | Pengujian unit dan fitur.                                             |
