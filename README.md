# Brand-nyamhap-db-project2
Repositori ini merupakan kelanjutan dari proyek basis data terintegrasi untuk Brand NyamHap, sebuah bisnis FnB yang berfokus pada peningkatan efisiensi operasional melalui sistem digital. Pada tahap ini, pengembangan difokuskan pada implementasi desain fisik basis data, sehingga sistem dapat berjalan secara optimal dan mendukung kebutuhan operasional bisnis secara nyata.

# Database Setup–Brand NyamHap
Untuk menjalankan aplikasi NyamHap, pastikan database dan lingkungan pengembangan sudah terkonfigurasi dengan benar. Berikut langkah-langkah setup:
1. Install XAMPP dan aktifkan modul Apache
- Digunakan untuk menjalankan server lokal dan mengeksekusi file PHP.
3. Gunakan PostgreSQL (via DBeaver atau tools lain).
- PostgreSQL digunakan sebagai sistem manajemen basis data.
- DBeaver membantu dalam visualisasi dan eksekusi query SQL.

# Persiapan
4. Import file SQL
- Buka file dummy-NYAMHAP.sql (kalau data base belum di buat).
- Jalankan semua script untuk membuat tabel dan mengisi data awal.
5. Buka Visual Studio Code (VS Code)
- Untuk mengedit file PHP seperti index.php, edit.php, dan lainnya.
- Menjalankan proyek lokal di folder htdocs/NYAMHAP (jika pakai XAMPP).
- Untuk mengedit file PHP seperti index.php, edit.php, dan lainnya.
- Menjalankan proyek lokal di folder htdocs/NYAMHAP (jika pakai XAMPP).
- Memastikan struktur folder, koneksi database, dan logika aplikasi bisa dikelola dengan nyaman.
- Langkah: Buka folder proyek NYAMHAP di VS Code.
# Implementasi Kode
5. Buat kode PHP sesuai struktur folder proyek
> config/
- [NYAMHAP/config/db.php](NYAMHAP/config/db.php) → Template koneksi database PostgreSQL
> layout/
- [NYAMHAP/config/header.php](NYAMHAP/layout/header.php) → Header HTML untuk semua halaman
- [NYAMHAP/config/footer.php](NYAMHAP/layout/footer.php) → Footer HTML untuk semua halaman
> menu/
- [NYAMHAP/menu/craete.php](NYAMHAP/menu/create.php) → Tambah menu
- [NYAMHAP/menu/edit.php](NYAMHAP/menu/edit.php) → Edit data menu
- [NYAMHAP/menu/delete.php](NYAMHAP/menu/delete.php) → Hapus menu
- [NYAMHAP/menu/index.php](NYAMHAP/menu/index.php) → Daftar menu
> pesanan/
- [NYAMHAP/pesanan/footer.php](NYAMHAP/pesanan/create.php) → Tambah pesanan
- [NYAMHAP/pesanan/edit.php](NYAMHAP/pesanan/edit.php) → Edit data pesanan
- [NYAMHAP/pesanan/delete.php](NYAMHAP/pesanan/delete.php) → Hapus pesanan
- [NYAMHAP/pesanan/index.php](NYAMHAP/pesanan/index.php) → Daftar pesanan
- [NYAMHAP/pesanan/proses_create.php](NYAMHAP/pesanan/proses_create.php) → Daftar pesanan
> pembayaran/
- [NYAMHAP/pembayaran/input.php](NYAMHAP/pembayaran/input.php) → Input pembayaran baru
- [NYAMHAP/pembayaran/edit.php](NYAMHAP/pembayaran/edit.php) → Edit status pembayaran
- [NYAMHAP/pembayaran/index.php](NYAMHAP/pembayaran/index.php) → Daftar pelanggan
> pelanggan/
- [NYAMHAP/pelanggan/craete.php](NYAMHAP/pelanggan/create.php) → Tambah pelanggan
- [NYAMHAP/pelanggan/edit.php](NYAMHAP/pelanggan/edit.php) → Edit data pelanggan
- [NYAMHAP/pelanggan/delete.php](NYAMHAP/pelanggan/delete.php) → Hapus pelanggan
- [NYAMHAP/pelanggan/index.php](NYAMHAP/pelanggan/index.php) → Daftar pelanggan
> laporan
- [NYAMHAP/laporan/index.php](NYAMHAP/laporan/index.php) → Tampilan laporan (penjualan, menu, dll)
> bahan_baku/
- [NYAMHAP/bahan_baku/craete.php](NYAMHAP/bahan_baku/create.php) → Tambah bahan_baku
- [NYAMHAP/bahan_baku/edit.php](NYAMHAP/bahan_baku/edit.php) → Edit data bahan_baku
- [NYAMHAP/bahan_baku/delete.php](NYAMHAP/bahan_baku/delete.php) → Hapus bahan_baku
- [NYAMHAP/bahan_baku/index.php](NYAMHAP/bahan_baku/index.php) → Daftar bahan_baku
> assests/img/
- 4K-removebg-preview.png (logo NYAMHAP)
> periklanan/
- [NYAMHAP/periklanan/craete.php](NYAMHAP/periklanan/create.php) → Tambah periklanan
- [NYAMHAP/periklanan/edit.php](NYAMHAP/periklanan/edit.php) → Edit data periklanan
- [NYAMHAP/periklanan/delete.php](NYAMHAP/periklanan/delete.php) → Hapus periklanan
- [NYAMHAP/periklanan/index.php](NYAMHAP/periklanan/index.php) → Daftar periklanan
> menu/
- [NYAMHAP/menu/craete.php](NYAMHAP/menu/create.php) → Tambah menu
- [NYAMHAP/menu/edit.php](NYAMHAP/menu/edit.php) → Edit data menu
- [NYAMHAP/menu/delete.php](NYAMHAP/menu/delete.php) → Hapus menu
- [NYAMHAP/menu/index.php](NYAMHAP/menu/index.php) → Daftar menu
> login.php
- [NYAMHAP/login.php](NYAMHAP/login.php) → Fitur Lofin (pakai data staff)
> logout.php
- [NYAMHAP/logout.php](NYAMHAP/logout.php) → Logout sesi
> index.php (index utama)
- [NYAMHAP/index.php](NYAMHAP/index.php) → Index utama


# Referensi 
Modul Proyek Praktikum Basis Data Semester Ganjil 2025
