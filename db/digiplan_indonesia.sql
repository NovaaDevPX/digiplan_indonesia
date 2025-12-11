DROP DATABASE IF EXISTS digiplan_indonesia;
CREATE DATABASE digiplan_indonesia;
USE digiplan_indonesia;

-- ============================
-- TABEL: roles
-- ============================
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles VALUES
(1, 'customer'),
(2, 'admin'),
(3, 'super_admin');

-- ============================
-- TABEL: users
-- ============================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_active TIMESTAMP NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

INSERT INTO users (id, name, email, password, role_id) VALUES
(1, 'Super Admin', 'superadmin@gmail.com', MD5('testing123'), 3),
(2, 'Admin', 'admin@gmail.com', MD5('testing123'), 2),
(3, 'Customer', 'customer@gmail.com', MD5('testing123'), 1);

-- ============================
-- TABEL: audit_trail
-- ============================
CREATE TABLE audit_trail (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  aksi TEXT NULL,
  tabel_yang_diubah VARCHAR(100) NULL,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================
-- TABEL: barang
-- ============================
CREATE TABLE barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_barang VARCHAR(100) NOT NULL,
  merk VARCHAR(100),
  warna VARCHAR(50),
  deskripsi TEXT,
  stok INT DEFAULT 0,
  harga DECIMAL(15,2) DEFAULT 0.00,
  nama VARCHAR(150),
  kontak VARCHAR(100),
  alamat VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- TABEL: permintaan_barang
-- ============================
CREATE TABLE permintaan_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  merk VARCHAR(100),
  warna VARCHAR(50),
  deskripsi TEXT,
  jumlah INT NOT NULL,
  status VARCHAR(50) DEFAULT 'Proses',
  tanggal_permintaan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  admin_id INT,
  tanggal_verifikasi DATETIME,
  catatan_admin TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO permintaan_barang 
(id, user_id, nama_barang, merk, warna, deskripsi, jumlah, status, tanggal_permintaan)
VALUES
(5, 2, 'TV', 'Samsung', 'hitam', '36 inc', 100, 'proses', '2025-11-01 12:59:36'),
(6, 2, 'Tumblr', 'Hydro Flask', 'Pink', '600ml', 50, 'proses', '2025-11-05 15:20:28'),
(7, 2, 'Laptop Lenovo Thinkpad', NULL, NULL, NULL, 0, 'proses', '2025-11-17 21:32:42'),
(8, 2, 'Laptop Lenovo Thinkpad', NULL, NULL, NULL, 0, 'proses', '2025-12-01 00:35:54');

-- ============================
-- TABEL: distribusi_barang
-- ============================
CREATE TABLE distribusi_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NULL,
  barang_id INT NULL,
  jumlah INT,
  tujuan VARCHAR(255),
  tanggal_pengiriman DATE,
  permintaan_id INT,
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id),
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id)
) ENGINE=InnoDB;

-- ============================
-- TABEL: notifikasi
-- ============================
CREATE TABLE notifikasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  permintaan_id INT NULL,
  pesan TEXT,
  status_baca TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id)
) ENGINE=InnoDB;

INSERT INTO notifikasi (id, pesan, created_at) VALUES
(1, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:21:21'),
(2, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:30:16'),
(3, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:30:20'),
(4, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:30:27'),
(5, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:32:09'),
(6, 'Ada permintaan barang baru dari customer.', '2025-11-05 15:32:30'),
(7, 'Ada permintaan barang baru dari customer.', '2025-11-15 02:05:17'),
(8, 'Ada permintaan barang baru dari customer.', '2025-11-15 02:07:37'),
(9, 'Ada permintaan barang baru dari customer.', '2025-11-15 02:15:38'),
(10, 'Ada permintaan barang baru dari customer.', '2025-11-15 02:24:27'),
(11, 'Ada permintaan barang baru dari customer.', '2025-11-15 05:59:48'),
(12, 'Ada permintaan barang baru dari customer.', '2025-11-21 02:44:02');

-- ============================
-- TABEL: chat
-- ============================
CREATE TABLE chat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengirim_id INT NOT NULL,
  penerima_id INT NOT NULL,
  pesan TEXT NOT NULL,
  tipe ENUM('text','image','audio','system') DEFAULT 'text',
  lampiran VARCHAR(255),
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  dibaca TINYINT(1) DEFAULT 0,
  group_id INT NULL
) ENGINE=InnoDB;

-- ============================
-- TABEL: produk
-- ============================
CREATE TABLE produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(255),
  harga INT,
  gambar VARCHAR(255),
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO produk VALUES
(2, 'Hydro Flask', 750000, '1764647749_daun.webp', 'Tumbler Hydro Flask 21oz Standar Flex Cap', '2025-12-02 03:55:49');

-- ============================
-- TABEL: keranjang
-- ============================
CREATE TABLE keranjang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  produk_id INT NOT NULL,
  jumlah INT DEFAULT 1,
  tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- TABEL: invoice
-- ============================
CREATE TABLE invoice (
  id_invoice INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  nomor_invoice VARCHAR(50) UNIQUE,
  tanggal_invoice DATE NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('belum bayar','lunas') DEFAULT 'belum bayar',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- TABEL: pembayaran
-- ============================
CREATE TABLE pembayaran (
  id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
  id_invoice INT NOT NULL,
  metode VARCHAR(50),
  jumlah DECIMAL(12,2),
  tanggal_bayar DATETIME,
  bukti_transfer VARCHAR(255),
  status ENUM('pending','berhasil','gagal') DEFAULT 'pending'
) ENGINE=InnoDB;

-- ============================
-- TABEL: pengadaan_barang
-- ============================
CREATE TABLE pengadaan_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT,
  barang_id INT,
  jumlah INT,
  harga_total DECIMAL(15,2),
  merk VARCHAR(100),
  warna VARCHAR(50),
  deskripsi_barang TEXT,
  nama VARCHAR(150),
  kontak VARCHAR(100),
  alamat VARCHAR(255),
  tanggal DATE,
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
) ENGINE=InnoDB;
