# DigiPlan Indonesia

**Sistem Pengadaan & Distribusi Barang**

DigiPlan Indonesia adalah sistem manajemen pengadaan barang berbasis web yang dirancang untuk mengelola alur **permintaan → pengadaan → distribusi → invoice → pembayaran**, lengkap dengan sistem notifikasi dan kontrol role.

---

## 🔐 Role & Hak Akses

| Role        | Akses                                                          |
| ----------- | -------------------------------------------------------------- |
| Customer    | Mengajukan permintaan barang, melihat status, menerima invoice |
| Admin       | Verifikasi permintaan, distribusi barang                       |
| Super Admin | Pengadaan barang, kontrol stok, supplier, laporan              |

---

## 🧱 Struktur Database

- Database: `digiplan_indonesia`
- DBMS: MySQL / MariaDB
- Engine: InnoDB

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

> Hampir seluruh tabel menggunakan **soft delete (`deleted_at`)**

---

## 🔄 Alur Sistem

Customer  
→ Permintaan Barang  
→ Admin Verifikasi  
→ **Super Admin (Procurement)**  
→ Distribusi  
→ Invoice  
→ Pembayaran

---

## ⭐ CORE LOGIC — PROCUREMENT (SUPER ADMIN)

Bagian ini adalah **inti sistem** dan tidak boleh diubah tanpa memahami alur bisnis.

---

### 1. Filter Permintaan Layak Diproses

Hanya permintaan:

- Status **disetujui**
- **Belum memiliki pengadaan**

Tujuan:

- Mencegah double procurement
- Menjaga konsistensi data

---

### 2. Cek Stok Otomatis (AJAX)

Saat Super Admin memilih permintaan:

- Sistem mengecek stok gudang
- Menentukan perlu atau tidaknya pengadaan

Endpoint:

```
ajax/get-barang-by-permintaan.php
```

---

### 3. Logic Stok (KRITIKAL)

- Jika stok ≥ permintaan → **cukup**
- Jika stok < permintaan → **kurang**
- Sistem otomatis menghitung jumlah pengadaan

Dampak:

- Tidak ada pengadaan berlebih
- Tidak ada kekurangan barang

---

### 4. Auto Fill Supplier (AMAN)

Supplier diambil dari:

- Pengadaan terakhir
- **Bukan** `STOK_GUDANG ( AUTO )`
- Data valid & aktif

Tujuan:

- Konsistensi supplier
- Hindari data palsu

---

### 5. Validasi Jumlah Minimum

Jika stok kurang:

- Jumlah pengadaan **tidak boleh di bawah kebutuhan**
- Field dikunci via `min_jumlah`

---

### 6. Hitung Harga Otomatis

```
total = jumlah × harga_satuan
```

Menghindari:

- Human error
- Inconsistent pricing

---

### 7. Reset Supplier (DISENGAJA)

Supplier di-reset setiap pilih permintaan.

Alasan:

- Tidak membawa data lama
- Data procurement tetap bersih

---

### 8. Notifikasi Realtime

Status:

- Success → stok cukup
- Warning → stok kurang
- Error → gagal ambil data

Menggunakan Alpine.js

---

## 🔐 Keamanan

- Role-based access control
- Prepared statement
- Validasi AJAX
- Soft delete untuk audit trail

---

## 🚀 Teknologi

- PHP Native
- MySQL (InnoDB)
- Tailwind CSS
- Alpine.js
- AJAX (Fetch API)

---

⚠️ **WARNING**  
Logic procurement adalah **core business rule**.  
Perubahan tanpa pemahaman dapat menyebabkan **kerusakan data bisnis**.

---

## 💳 PAYMENT GATEWAY — MIDTRANS (SANDBOX)

Sistem DigiPlan Indonesia menggunakan **Midtrans Payment Gateway** untuk proses pembayaran invoice secara **online & realtime**.

### Mode yang Digunakan

- Environment : **SANDBOX**
- Tujuan : Development & testing
- Tidak menggunakan uang asli

---

### 🔑 Konfigurasi Midtrans

Midtrans dikonfigurasi menggunakan:

- **Server Key (Sandbox)**
- **Client Key (Sandbox)**

⚠️ **Catatan Keamanan**

- Server Key **hanya digunakan di backend**
- Client Key **hanya digunakan di frontend**
- Jangan pernah commit Server Key ke repository publik

---

### 🔄 Flow Pembayaran

```
Customer
   ↓
Klik Bayar Invoice
   ↓
Midtrans Snap Popup
   ↓
Customer Selesaikan Pembayaran
   ↓
Midtrans Kirim Callback (Webhook)
   ↓
Server Validasi Signature
   ↓
Update Status Invoice & Pembayaran
```

---

### 📡 Midtrans Callback / Webhook

Sistem menerima notifikasi otomatis dari Midtrans melalui **Callback URL**.

Callback ini digunakan untuk:

- Menentukan status pembayaran (`pending`, `settlement`, `expire`, `cancel`)
- Menyimpan data pembayaran ke database
- Mengubah status invoice menjadi **lunas** jika pembayaran berhasil

---

### 🌐 Cloudflare Tunnel (KRITIKAL)

Karena sistem berjalan di **local / private server**, digunakan **Cloudflare Tunnel** agar Midtrans dapat mengakses endpoint callback.

#### Fungsi Cloudflare Tunnel:

- Mengekspos endpoint lokal ke internet secara aman
- Tanpa perlu VPS / public IP
- HTTPS otomatis

Contoh endpoint callback:

```
https://xxxx.trycloudflare.com/digiplan_indonesia/midtrans/callback.php
```

📌 **Kenapa ini penting?**

- Midtrans **wajib** mengirim webhook ke URL publik
- Localhost **tidak bisa diakses** oleh Midtrans

---

### 🔐 Validasi Signature Key (WAJIB)

Setiap callback Midtrans diverifikasi menggunakan **Signature Key**:

```
sha512(order_id + status_code + gross_amount + server_key)
```

Tujuan:

- Mencegah request palsu
- Menjamin data berasal dari Midtrans

Jika signature tidak valid:

- Callback **ditolak**
- Database **tidak diubah**

---

### 🗃️ Dampak ke Database

Saat pembayaran berhasil (`settlement`):

- Tabel `pembayaran`

  - status → `berhasil`
  - metode → midtrans
  - tanggal_bayar → otomatis

- Tabel `invoice`
  - status → `lunas`

Jika `pending`:

- Invoice tetap `belum bayar`

Jika `expire / cancel`:

- Status pembayaran `gagal`

---

### ⚠️ Catatan Penting Midtrans

- Mode Sandbox **tidak untuk produksi**
- Pastikan:
  - Callback URL aktif
  - Tunnel tidak mati
  - Server Key sesuai environment
- Setiap restart tunnel → **URL BERUBAH**
  - Harus update di Dashboard Midtrans

---

### 🚀 Rekomendasi Produksi

Untuk production:

- Gunakan **Midtrans Production**
- Gunakan domain resmi
- Jangan gunakan Cloudflare Tunnel
- Simpan key di `.env`

---

---

## 🌐 Menjalankan Cloudflare Tunnel (WAJIB UNTUK MIDTRANS CALLBACK)

Jika **belum memiliki Cloudflare Tunnel aktif**, maka **WAJIB menjalankan tunnel terlebih dahulu** agar Midtrans dapat mengirim callback ke server lokal.

### 1️⃣ Install Cloudflared

Pastikan `cloudflared` sudah terinstall di sistem.

Cek instalasi:

```
cloudflared --version
```

---

### 2️⃣ Jalankan Tunnel ke Localhost

Gunakan perintah berikut:

```
cloudflared tunnel --url http://localhost:80
```

📌 Penjelasan:

- `http://localhost:80` → alamat aplikasi lokal
- Cloudflare akan memberikan **URL publik HTTPS**
- Contoh:

```
https://random-name.trycloudflare.com
```

---

### 3️⃣ Set Callback URL di Midtrans

Gabungkan URL tunnel dengan endpoint callback:

```
https://random-name.trycloudflare.com/digiplan_indonesia/midtrans/callback.php
```

Masukkan URL ini ke:

- Midtrans Dashboard → Sanbox → Settings → Payments → Payment Notification URL

---

### ⚠️ Catatan Penting

- URL tunnel **BERUBAH setiap restart**
- Jika tunnel mati:
  - Callback Midtrans GAGAL
  - Status pembayaran tidak update
- Pastikan tunnel **aktif saat testing pembayaran**

---

### ✅ Checklist Sebelum Testing Midtrans

- [ ] Cloudflare tunnel aktif
- [ ] Callback URL sudah di-update di Midtrans
- [ ] Server Key sesuai Sandbox
- [ ] Signature validation aktif
- [ ] Endpoint callback bisa diakses via browser

---

