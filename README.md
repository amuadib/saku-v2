# Saku v2

Saku v2 adalah Sistem Informasi Akademik dan Administrasi Sekolah berbasis Laravel yang dirancang untuk mengelola berbagai kebutuhan operasional pendidikan. Sistem ini mendukung multi-lembaga/yayasan (SDI, SMPI, SMAI) dan menawarkan dua antarmuka pengguna utama: Dasbor Admin untuk staf/pengelola dan Dasbor Mobile untuk siswa/santri.

## Fitur Utama

### 1. Dasbor Admin
Antarmuka web lengkap untuk staf dan administrator sekolah dengan berbagai modul:
- **Manajemen Data Induk:** Siswa, Kelas, Periode, dan *User*
- **Keuangan & Transaksi:** Kas, Tabungan Siswa, Tagihan, Pembelian, dan Penjualan
- **Sistem Pembayaran:** Pembayaran SPP/Tagihan dan Riwayat Transaksi (Buku Besar)
- **Modul Lainnya:** Pengaduan, Barang, Supplier, Log Aktivitas
- **Laporan:** Cetak tagihan, kuitansi, dan histori transaksi

### 2. Dasbor Siswa (Mobile-First)
Antarmuka ringan dan responsif (berbasis *mobile-view*) yang dirancang khusus untuk siswa (diakses menggunakan *role_id* Siswa):
- **Ringkasan Akun:** Cek saldo tabungan secara *real-time*
- **Akses Layanan Cepat:** Tagihan, Tabungan, Riwayat Transaksi, Absensi, dan Kartu Digital
- **Pusat Informasi:** Pembaruan berita dan pengumuman sekolah
- **Profil Mandiri:** Manajemen akun, ubah kata sandi, dan dukungan 

## Teknologi yang Digunakan
Sistem ini dikembangkan dengan *stack* modern:
- **Framework:** [Laravel 11](https://laravel.com/)
- **Frontend / Reaktivitas:** [Livewire v3](https://livewire.laravel.com/) (menggunakan komponen reguler dan Volt)
- **Styling / UI:** [Tailwind CSS](https://tailwindcss.com/) terintegrasi dengan [Flux UI](https://fluxui.dev/)
- **Database:** MySQL / MariaDB

## Konfigurasi
Pengaturan spesifik instansi seperti daftar lembaga (SD, SMP, SMA, dll), tingkat, dan konfigurasi kustom lainnya dapat ditemukan pada `config/custom.php`.

## Panduan Instalasi (Step-by-Step)

Ikuti langkah-langkah di bawah ini untuk menginstal dan menjalankan proyek di *local machine* Anda:

### 1. Prasyarat (*Prerequisites*)
Pastikan Anda sudah menginstal aplikasi berikut di perangkat Anda:
- **PHP** (minimal versi 8.2)
- **Composer** (untuk *package management* PHP)
- **Node.js & npm** (minimal Node v18)
- **Database Server** (MySQL/MariaDB, bisa menggunakan XAMPP/Laragon)
- **Git**

### 2. Langkah Instalasi

**Langkah 1: Kloning Repositori**
Buka terminal dan jalankan perintah:
```bash
git clone https://github.com/amuadib/saku-v2
cd saku-v2
```

**Langkah 2: Instalasi Dependensi PHP**
Gunakan composer untuk menginstal library PHP yang dibutuhkan:
```bash
composer install
```

**Langkah 3: Menyiapkan File Konfigurasi (Environment)**
Salin file `.env.example` menjadi `.env`:
- Di Linux/Mac: `cp .env.example .env`
- Di Windows: `copy .env.example .env`

**Langkah 4: Konfigurasi Database**
Buka file `.env` di teks editor Anda, lalu sesuaikan konfigurasi *database* (pastikan Anda sudah membuat *database* kosong di MySQL/phpMyAdmin dengan nama, misal, `saku_v2`):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=password_database_jika_ada
```

**Langkah 5: Generate Application Key**
Jalankan perintah berikut untuk meng-_generate_ APP_KEY keamanan Laravel:
```bash
php artisan key:generate
```

**Langkah 6: Migrasi & Seeding Database**
Buat struktur tabel ke *database* sekaligus mengisi data bawaan (akun admin, dsb):
```bash
php artisan migrate --seed
```

**Langkah 7: Instalasi Dependensi Frontend**
Instal library berbasis JavaScript dan CSS:
```bash
npm install
```

**Langkah 8: Menghubungkan *Storage***
Jalankan perintah ini agar file *upload* (foto siswa/dokumen) dapat diakses:
```bash
php artisan storage:link
```

### 3. Menjalankan Aplikasi
Buka **dua terminal** terpisah pada *folder project* ini.

Di **Terminal 1** (untuk backend PHP):
```bash
php artisan serve
```

Di **Terminal 2** (untuk frontend/Vite reaktivitas CSS & JS):
```bash
npm run dev
```

Aplikasi sekarang sudah berjalan! Silakan buka *browser* Anda dan kunjungi `http://localhost:8000`.
