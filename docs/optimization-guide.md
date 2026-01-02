# Panduan Optimasi Aplikasi Percetakan Mutiara Rizki

Dokumen ini berisi panduan konfigurasi untuk memaksimalkan performa aplikasi Laravel + Filament pada XAMPP Windows.

---

## 1. Laravel Caching Commands

Jalankan perintah berikut setelah deploy atau setelah perubahan konfigurasi:

```bash
# Cache konfigurasi aplikasi
php artisan config:cache

# Cache route untuk mempercepat routing
php artisan route:cache

# Cache view untuk mempercepat render blade
php artisan view:cache

# Optimasi Filament (khusus untuk Filament v4)
php artisan filament:optimize

# Atau gunakan perintah all-in-one
php artisan optimize
```

### Clear Cache (saat development)

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## 2. Konfigurasi PHP (php.ini)

Lokasi file: `C:\xampp\php\php.ini`

### OPcache Settings

Aktifkan dan konfigurasi OPcache untuk mempercepat eksekusi PHP:

```ini
; Aktifkan OPcache
opcache.enable=1
opcache.enable_cli=1

; Memory untuk caching bytecode
opcache.memory_consumption=128

; Jumlah file yang bisa di-cache
opcache.max_accelerated_files=10000

; Validasi timestamp (set 0 di production)
opcache.validate_timestamps=1
opcache.revalidate_freq=2

; Optimasi JIT (PHP 8.0+)
opcache.jit=1255
opcache.jit_buffer_size=100M
```

### Memory Limit

Tingkatkan memory limit untuk proses berat seperti generate PDF:

```ini
memory_limit=512M
```

### Upload File Limits

Jika aplikasi memerlukan upload file besar:

```ini
upload_max_filesize=50M
post_max_size=50M
```

### Execution Time

Untuk proses lama seperti export laporan:

```ini
max_execution_time=300
```

---

## 3. Konfigurasi MySQL (my.ini)

Lokasi file: `C:\xampp\mysql\bin\my.ini`

### InnoDB Buffer Pool

```ini
[mysqld]
# Alokasikan 50-70% RAM untuk buffer pool
innodb_buffer_pool_size=512M

# Query cache
query_cache_type=1
query_cache_size=64M
query_cache_limit=2M
```

---

## 4. Konfigurasi .env Laravel

Pastikan konfigurasi berikut di file `.env`:

```env
# Gunakan MySQL sebagai cache driver
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Atau gunakan file untuk performa lebih baik
CACHE_STORE=file
SESSION_DRIVER=file

# Nonaktifkan debug di production
APP_DEBUG=false
APP_ENV=production
```

---

## 5. Database Indexes

Migration berikut sudah ditambahkan untuk optimasi query:

```php
// Tabel orders
$table->index('order_number');     // Pencarian invoice
$table->index('payment_status');   // Filter status pembayaran
$table->index('production_status'); // Filter status produksi
$table->index('customer_id');      // Join dengan customers
$table->index('status');           // Filter status pesanan
$table->index('order_date');       // Filter tanggal
$table->index('created_at');       // Sort default

// Tabel transactions
$table->index('account_id');       // Join dengan accounts
$table->index('transaction_date'); // Filter laporan periode
$table->index('order_id');         // Join dengan orders
```

Jalankan migration:

```bash
php artisan migrate
```

---

## 6. Restart Services

Setelah mengubah konfigurasi, restart XAMPP:

1. Buka **XAMPP Control Panel**
2. Klik **Stop** pada Apache dan MySQL
3. Klik **Start** kembali

Atau via command line:

```bash
# Windows
net stop Apache2.4
net start Apache2.4
net stop MySQL
net start MySQL
```

---

## 7. Verifikasi Optimasi

### Cek OPcache aktif

```bash
php -i | findstr opcache
```

### Cek memory limit

```bash
php -i | findstr memory_limit
```

### Cek Laravel caches

```bash
php artisan about
```

---

## Troubleshooting

### Error: Maximum execution time exceeded

Tingkatkan `max_execution_time` di `php.ini` atau tambahkan di awal script:

```php
set_time_limit(300);
```

### Error: Allowed memory size exhausted

Tingkatkan `memory_limit` di `php.ini` atau di kode:

```php
ini_set('memory_limit', '512M');
```

### Page load lambat

1. Pastikan cache Laravel aktif: `php artisan optimize`
2. Cek N+1 query dengan Laravel Debugbar
3. Pastikan index database sudah dijalankan
