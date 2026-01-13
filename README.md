# Aplikasi Laporan Keuangan - Percetakan Mutiara Rizki

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Sistem Informasi Laporan Keuangan Berbasis Web</strong><br>
  Dibangun dengan Framework Laravel
</p>

---

## 📋 Deskripsi Aplikasi

Aplikasi Laporan Keuangan Percetakan Mutiara Rizki adalah sistem informasi berbasis web yang dirancang untuk mengelola seluruh aspek keuangan usaha percetakan. Sistem ini menyediakan fitur lengkap mulai dari pencatatan data master, manajemen transaksi pesanan, hingga pembuatan laporan keuangan yang komprehensif.

---

## ✨ Fitur Utama

### 🔐 Autentikasi Pengguna
Sistem login yang aman dengan fitur:
- Email dan password authentication
- Fitur "Remember Me" untuk kemudahan akses
- Session management untuk keamanan data

![Menu Login](SS%20APLIKASI/MENU%20LOGIN.JPG)

---

### 📊 Dashboard
Dashboard interaktif yang menampilkan ringkasan keuangan secara real-time:
- **Omset Bulan Ini** - Total pendapatan kotor bulanan
- **HPP Bulan Ini** - Harga Pokok Penjualan
- **Laba Kotor** - Selisih omset dan HPP dengan persentase margin
- **Saldo Kas** - Posisi kas saat ini
- **Pendapatan Hari Ini** - Tracking pendapatan harian
- **Pendapatan Bulan Ini** - Akumulasi pendapatan bulanan
- **Laba Bersih** - Laba/Rugi bersih periode berjalan

![Dashboard](SS%20APLIKASI/DASHBOARD.JPG)

---

## 📁 Master Data

### 👥 Menu Pelanggan
Manajemen data pelanggan dengan fitur:
- Pencatatan nama, nomor telepon, dan alamat lengkap
- Tracking jumlah pesanan per pelanggan
- Fungsi pencarian dan filter data
- Tambah, edit, dan hapus data pelanggan

![Menu Pelanggan](SS%20APLIKASI/MENU%20PELANGGAN.JPG)

---

### 🏭 Menu Produk
Katalog produk percetakan dengan informasi:
- Nama produk dan satuan
- Tipe harga (Per Area, Per Unit, dll)
- Harga jual produk
- Deskripsi spesifikasi produk
- Manajemen CRUD produk

![Menu Produk](SS%20APLIKASI/MENU%20PRODUK.JPG)

---

### 📦 Menu Bahan Baku
Pengelolaan inventori bahan baku:
- Data bahan baku (HVS, Art Paper, Art Carton, dll)
- Jumlah stok dan satuan
- Harga beli per satuan
- Status aktif/non-aktif bahan
- Fitur tambah stok
- Tracking stok minimum

![Menu Bahan Baku](SS%20APLIKASI/MENU%20BAHAN%20BAKU.JPG)

---

## 📝 Transaksi

### 🛒 Menu Pesanan
Manajemen pesanan pelanggan dengan fitur:
- Nomor pesanan otomatis
- Data pelanggan terkait
- Tanggal pesanan dan deadline
- Status pesanan (Menunggu, Selesai)
- Status produksi (Antrian, Selesai)
- Status pembayaran (Sebagian, Belum Bayar, Lunas)
- Total nilai pesanan

![Menu Pesanan](SS%20APLIKASI/MENU%20PESANAN.JPG)

---

## 📈 Laporan Keuangan

### 📑 Rekap Laporan
Laporan keuangan komprehensif dengan:
- Filter periode (Tanggal Mulai - Tanggal Akhir)
- Opsi tampilkan beban operasional
- Ringkasan Total Omset, Total HPP, Laba Kotor
- Rekap laporan keuangan detail
- Export laporan ke PDF/Excel

![Rekap Laporan](SS%20APLIKASI/REKAP%20LAPORAN.JPG)

---

### 📊 Neraca
Laporan posisi keuangan yang mencakup:

**ASET**
- 111 - Kas
- 112 - Piutang Usaha
- 113 - Persediaan Bahan
- 121 - Peralatan
- 122 - Akumulasi Penyusutan Peralatan

**Total Aset** dengan kalkulasi otomatis

![Neraca](SS%20APLIKASI/NERACA.JPG)

---

### 📉 Analisis Margin Produk
Analisis profitabilitas per produk:
- Filter periode laporan
- Quantity terjual per produk
- Pendapatan per produk
- Harga dasar dan harga jual rata-rata
- Perhitungan Margin %
- Kontribusi % terhadap total pendapatan
- Export laporan analisis

![Analisis Margin Produk](SS%20APLIKASI/ANALISI%20MARGIN%20PRODUK.JPG)

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| Backend Framework | Laravel |
| Frontend | Blade Template + CSS |
| Database | MySQL/MariaDB |
| Authentication | Laravel Sanctum/Built-in Auth |
| Reporting | Laravel Excel/DomPDF |

---

## 📦 Struktur Menu Aplikasi

```
📁 Percetakan Mutiara Rizki
├── 📊 Dashboard
├── 📑 Laporan
│   ├── Rekap Laporan
│   ├── Neraca
│   └── Analisis Margin Produk
├── 📁 Master Data
│   ├── Pelanggan
│   ├── Produk
│   └── Bahan Baku
└── 📝 Transaksi
    └── Pesanan
```

---

## 🚀 Instalasi

### Prasyarat
- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Node.js & NPM

### Langkah Instalasi

```bash
# Clone repository
git clone https://github.com/x-why-z/Laporan-Keuangan-Laravel.git

# Pindah ke direktori proyek
cd Percetakan-Mutiara-Rizki

# Install dependencies
composer install
npm install

# Salin file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Konfigurasi database di file .env

# Jalankan migrasi dan seeder
php artisan migrate --seed

# Compile assets
npm run dev

# Jalankan server
php artisan serve
```

---

## 📄 Lisensi

Aplikasi ini dikembangkan menggunakan framework [Laravel](https://laravel.com) yang dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).

---

<p align="center">
  <strong>Percetakan Mutiara Rizki</strong><br>
  © 2026 - Sistem Informasi Laporan Keuangan
</p>
