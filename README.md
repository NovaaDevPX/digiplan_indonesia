# DigiPlan Indonesia

**Sistem Pengadaan & Distribusi Barang**

DigiPlan Indonesia adalah sistem manajemen pengadaan barang berbasis web
yang dirancang untuk mengelola alur **permintaan → pengadaan → distribusi → invoice → pembayaran**, lengkap dengan sistem notifikasi,
kontrol role, dan dokumentasi instalasi lengkap.

---

# 📦 Instalasi DigiPlan Indonesia

Panduan instalasi lengkap untuk development environment.

## 1. Persyaratan Sistem

### Software Utama

- PHP ≥ 8.1
- MySQL / MariaDB
- Apache (XAMPP / Laragon / LAMP)
- Composer (opsional)
- Cloudflared (untuk Midtrans callback)

### Ekstensi PHP

- mysqli
- curl
- json
- openssl
- mbstring

---

## 2. Download / Clone Project

Clone via Git:

```
git clone https://github.com/NovaaDevPX/digiplan_indonesia.git
```

Atau download ZIP ke:

```
htdocs/digiplan_indonesia
```

---

## 3. Buat Database

1. Buka phpMyAdmin
2. Buat database:

```
digiplan_indonesia
```

3. Import:

```
digiplan_indonesia.sql
```

Tabel utama:

- roles
- users
- barang
- permintaan_barang
- pengadaan_barang
- distribusi_barang
- invoice
- pembayaran
- notifikasi

> Hampir semua tabel menggunakan **soft delete (`deleted_at`)**

---

## 4. Konfigurasi Koneksi Database

Edit:

```
include/conn.php
```

Isi:

```php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "digiplan_indonesia";
```

---

## 5. Set Base URL (WAJIB)

Edit:

```
include/base-url.php
```

```php
$base_url = "http://localhost/digiplan_indonesia/";
```

---

## 6. Konfigurasi Midtrans (Sandbox)

File:

```
midtrans/config.php
```

Isi:

```php
$MIDTRANS_SERVER_KEY = "YOUR_SERVER_KEY_SANDBOX";
$MIDTRANS_CLIENT_KEY = "YOUR_CLIENT_KEY_SANDBOX";
$snapUrl = "https://app.sandbox.midtrans.com/snap/v1/transactions";
```

Catatan:

- Server Key → backend
- Client Key → frontend
- Jangan commit Server Key ke repo publik

---

## 7. Set Callback Midtrans

Format file:

```
midtrans/callback.php
```

URL dihubungkan setelah Tunnel aktif.

---

## 8. Jalankan Server

### XAMPP:

- Start Apache & MySQL  
  Akses:

```
http://localhost/digiplan_indonesia
```

### Laragon:

```
http://digiplan_indonesia.test
```

---

## 9. Default Login

| Role        | Email          | Password |
| ----------- | -------------- | -------- |
| Super Admin | admin@local    | 123456   |
| Admin       | admin2@local   | 123456   |
| Customer    | customer@local | 123456   |

---

## 10. Struktur Direktori

```
digiplan_indonesia/
│
├── include/
│   ├── conn.php
│   ├── auth.php
│   ├── base-url.php
│   └── functions.php
│
├── superadmin/
│   ├── procurement.php
│   ├── invoice-create.php
│   └── ajax/
│       └── get-barang-by-permintaan.php
│
├── admin/
├── customer/
├── midtrans/
│   ├── config.php
│   ├── callback.php
│   └── snap.php
│
└── assets/
    ├── css/
    ├── js/
    └── img/
```

---

# 🔐 Role & Hak Akses

| Role        | Akses                                                          |
| ----------- | -------------------------------------------------------------- |
| Customer    | Mengajukan permintaan barang, melihat status, menerima invoice |
| Admin       | Verifikasi permintaan, distribusi barang                       |
| Super Admin | Pengadaan barang, kontrol stok, supplier, laporan              |

---

# ⭐ CORE LOGIC – PROCUREMENT (Super Admin)

Procurement adalah inti sistem.

### 1. Filter Permintaan Layak

- Status: disetujui
- Belum memiliki pengadaan

### 2. Cek Stok Otomatis (AJAX)

Endpoint:

```
ajax/get-barang-by-permintaan.php
```

### 3. Logic Stok

- stok ≥ permintaan → cukup
- stok < permintaan → kurang
- Sistem otomatis menghitung jumlah pengadaan

### 4. Auto Fill Supplier

Mengambil dari pengadaan terakhir, kecuali `STOK_GUDANG ( AUTO )`.

### 5. Validasi Minimum

Jumlah pengadaan harus ≥ kebutuhan.

### 6. Hitung Harga Otomatis

```
total = jumlah × harga_satuan
```

### 7. Reset Supplier

Direset setiap memilih permintaan baru.

### 8. Notifikasi Realtime

- success
- warning
- error  
  Menggunakan Alpine.js

---

# 🔐 Keamanan

- Role-based access
- Prepared statement
- Validasi AJAX
- Soft delete

---

# 💳 Midtrans Payment Gateway (SANDBOX)

### Alur Pembayaran

```
Customer → Klik Bayar → Snap Popup → Pembayaran → Callback → Update Status
```

### Callback digunakan untuk:

- Menentukan pembayaran (`pending`, `settlement`, `expire`, `cancel`)
- Mengisi tabel pembayaran
- Mengubah status invoice

---

# 🌐 Cloudflare Tunnel (WAJIB)

Untuk menjalankan callback di lokal.

### Install:

```
winget install Cloudflare.cloudflared
```

### Jalankan tunnel:

```
cloudflared tunnel --url http://localhost:80
```

Akan menghasilkan:

```
https://random-name.trycloudflare.com
```

Callback di Midtrans:

```
https://random-name.trycloudflare.com/digiplan_indonesia/midtrans/callback.php
```

---

# 🧪 Testing Pembayaran Checklist

- [ ] Tunnel aktif
- [ ] Callback URL sesuai
- [ ] Server Key benar
- [ ] Signature valid
- [ ] Endpoint callback dapat diakses

---

# 🚀 Siap Produksi

- Gunakan Midtrans Production
- Gunakan domain HTTPS
- Jangan gunakan tunnel
- Simpan key di `.env`

---

# 📝 Checklist Instalasi

- [ ] Database import
- [ ] conn.php konfigurasi
- [ ] base-url valid
- [ ] Midtrans Sandbox aktif
- [ ] Callback URL terpasang
- [ ] Tunnel berjalan
- [ ] Invoice berhasil dibayar
- [ ] Callback update status

