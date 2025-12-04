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
- [config/db.php](config/db.php) → Template koneksi database PostgreSQL
> layout/
- header.php → Header HTML untuk semua halaman
- footer.php → Footer HTML untuk semua halaman
> menu/
- create.php → Tambah staff
- dit.php → Edit data staff
- delete.php → Hapus staff
- index.php → Daftar staff
> pesanan/
- create.php → Form tambah pesanan
- edit.php → Edit detail pesanan
- delete.php → Hapus pesanan
- index.php → Daftar pesanan
- proses_create.php → Proses simpan pesanan baru
> pembayaran/
- input.php → Input pembayaran baru
- edit.php → Edit status pembayaran
- delete.php → Hapus data pembayaran
> pelanggan/
- create.php → Tambah pelanggan
- dit.php → Edit data pelanggan
- delete.php → Hapus pelanggan
- index.php → Daftar pelanggan
> laporan
- index.php → Tampilan laporan (penjualan, menu, dll)
> bahan_baku/
- create.php → Tambah bahan_baku baru
- dit.php → Edit data bahan_baku
- delete.php → Hapus bahan_baku
- index.php → Daftar bahan_baku
> assests/img/
- 4K-removebg-preview.png (logo NYAMHAP)
> periklanan/
- create.php → Tambah periklanan baru
- edit.php → Edit data periklanan
- delete.php → Hapus periklanan
- index.php → Tampilkan periklanan
> menu/
- create.php → Tambah menu baru
- edit.php → Edit data menu
- delete.php → Hapus menu
- index.php → Tampilkan daftar menu

Kode ?PHP > NYAMHAP
https://drive.google.com/drive/folders/1V8VLMPAx1iLph5At27mOljJ7roCSK9KF?usp=sharing
