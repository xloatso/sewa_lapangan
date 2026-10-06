# MAUMAIN — Reservasi Lapangan

Adaptasi proyek lokal MAUJAJAN: katalog publik tanpa login, filter kategori, formulir pemesan, review melalui dialog, lalu dashboard pengelola. Nama MAUMAIN digunakan untuk konteks olahraga. Dokumen kebutuhan: `Sistem Reservasi Lapangan Futsal 2.docx`.

## Pelanggan

1. Buka `/`, pilih tanggal dan klik **Cek jadwal**.
2. Filter Futsal, Badminton, atau Basket, lalu pilih lapangan.
3. Pilih jam mulai dan durasi 1–4 jam, dalam jam operasional 08.00–22.00 WIB. Reservasi maksimal 3 bulan ke depan.
4. Isi nama dan nomor telepon, klik **Review reservasi**, periksa rincian, lalu kirim.
5. Simpan nomor reservasi dari konfirmasi. Status awal **Pending**. Pembayaran ditangani petugas, bukan payment gateway.

Satu pengajuan memuat satu lapangan. Server menghitung harga dari tarif database. Pending dan Lunas menahan jadwal; Batal membebaskan jadwal. Pengaktifan kembali reservasi batal diperiksa terhadap jadwal terbaru. Jadwal lampau dan bentrok ditolak. Jam menggunakan `APP_TIMEZONE`, default Asia/Jakarta (UTC+7).

## Admin

1. Buat akun dengan `php artisan admin:create admin@lapangan.test`; masukkan kata sandi minimal 12 karakter saat diminta.
2. Masuk lewat `/login`. Tidak ada registrasi admin publik.
3. `/admin/dashboard`: lihat pelanggan, lapangan, jadwal, total, filter status, dan ubah Pending/Lunas/Batal.
4. `/admin/courts`: tambah, edit, upload foto JPG/PNG/WebP maksimal 2 MB, atau hapus lapangan tanpa riwayat reservasi.

## Menjalankan

```sh
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan admin:create admin@lapangan.test
php artisan serve
```

Buka http://127.0.0.1:8000. Data contoh adalah empat lapangan; seeder tidak menimpa nama yang sudah ada. Seeder admin mengikuti MAUJAJAN: `User::create` dengan email `admin@gmail.com` dan kata sandi `password123`. Jalankan dengan `php artisan db:seed --class=AdminSeeder`. Jalankan sekali; email yang sudah terdaftar akan ditolak oleh constraint unique. CSS/JavaScript aplikasi ada di `public/css/reservation.css` dan `public/js/reservation.js`, sehingga halaman ini tidak membutuhkan Vite untuk berjalan.

Database aktif mengikuti `.env`: MySQL, database `lapangan`. Struktur `courts`, `bookings`, dan `booking_details` mengikuti dokumen. User mendapat flag `is_admin`. Untuk memakai nama database MySQL `db_reservasi_lapangan` dari dokumen, siapkan database dan ubah konfigurasi koneksi sebelum menjalankan migrasi di database tersebut.

## Verifikasi

`php artisan test` memeriksa pembuatan reservasi, harga server, bentrok dan slot berdampingan, pembatalan/aktivasi ulang, validasi tanggal/jam, akses admin, login/logout, CRUD, upload foto, dan perlindungan riwayat.

## Susunan seeder

- `DatabaseSeeder`: menjalankan `AdminSeeder` dan `CourtSeeder`.
- `AdminSeeder`: membuat akun `admin@gmail.com` menggunakan `User::create` dan `Hash::make`, dengan `is_admin = true`. Tidak memiliki pembatasan environment atau pengecekan akun lama, mengikuti MAUJAJAN.
- `CourtSeeder`: mengisi empat lapangan contoh sesuai kolom `courts`, tanpa menimpa data yang sudah ada.
- `bookings` dan `booking_details` diisi lewat reservasi pelanggan, bukan data transaksi contoh.

Jalankan `php artisan migrate --seed` untuk menyiapkan tabel dan data awal tanpa menghapus data.
