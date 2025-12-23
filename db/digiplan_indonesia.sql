DROP DATABASE IF EXISTS digiplan_indonesia;
CREATE DATABASE digiplan_indonesia;
USE digiplan_indonesia;

-- ============================
-- ROLES (STATIC)
-- ============================
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles VALUES
(1,'customer'),
(2,'admin'),
(3,'super_admin');

-- ============================
-- USERS (SOFT DELETE)
-- ============================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

INSERT INTO users VALUES
(1,'Super Admin','superadmin@gmail.com',MD5('123'),3,NOW(),NULL),
(2,'Admin Gudang','admin@gmail.com',MD5('123'),2,NOW(),NULL),
(3,'PT Maju Jaya','customer1@gmail.com',MD5('123'),1,NOW(),NULL),
(4,'CV Sukses Abadi','customer2@gmail.com',MD5('123'),1,NOW(),NULL),
(5,'PT Sejahtera','customer3@gmail.com',MD5('123'),1,NOW(),NULL);

-- ============================
-- BARANG
-- ============================
CREATE TABLE barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_barang VARCHAR(100),
  merk VARCHAR(100),
  warna VARCHAR(50),
  deskripsi TEXT,
  stok INT,
  harga DECIMAL(15,2),
  gambar VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ============================
-- PERMINTAAN BARANG
-- ============================
CREATE TABLE permintaan_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_permintaan VARCHAR(50) UNIQUE,
  user_id INT,
  nama_barang VARCHAR(100),
  merk VARCHAR(100),
  warna VARCHAR(100),
  jumlah INT,
  status ENUM(
    'diajukan','ditolak','dibatalkan',
    'disetujui','dalam_pengadaan',
    'siap_distribusi','selesai'
  ),
  admin_id INT,
  tanggal_verifikasi DATETIME,
  catatan_admin TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================
-- PENGADAAN BARANG
-- ============================
CREATE TABLE pengadaan_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_pengadaan VARCHAR(50) UNIQUE,
  permintaan_id INT NOT NULL,
  admin_id INT NOT NULL,
  barang_id INT DEFAULT NULL,

  nama_barang VARCHAR(100),
  merk VARCHAR(100),
  warna VARCHAR(100),
  jumlah INT,

  supplier VARCHAR(150),
  kontak_supplier VARCHAR(100),
  alamat_supplier VARCHAR(255),

  harga_satuan DECIMAL(15,2),
  harga_total DECIMAL(15,2),

  status_pengadaan ENUM('diproses','selesai','dibatalkan'),
  tanggal_pengadaan DATE,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,

  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id),
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================
-- DISTRIBUSI BARANG
-- ============================
CREATE TABLE distribusi_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_distribusi VARCHAR(50) UNIQUE,
  pengadaan_id INT,
  permintaan_id INT,
  admin_id INT,

  harga_satuan INT NOT NULL DEFAULT 0,
  harga_total INT NOT NULL DEFAULT 0,
  sumber_harga ENUM('pengadaan','stok_gudang') NOT NULL DEFAULT 'pengadaan',

  alamat_pengiriman VARCHAR(255),
  kurir VARCHAR(100),
  no_resi VARCHAR(100),
  tanggal_kirim DATE,
  tanggal_terima DATE,

  status_distribusi ENUM('siap_dikirim','dikirim','diterima','dibatalkan'),

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,

  FOREIGN KEY (pengadaan_id) REFERENCES pengadaan_barang(id),
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id),
  FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================
-- INVOICE
-- ============================
CREATE TABLE invoice (
  id_invoice INT AUTO_INCREMENT PRIMARY KEY,
  distribusi_id INT,
  nomor_invoice VARCHAR(50) UNIQUE,
  tanggal_invoice DATE,
  jatuh_tempo DATE,
  total DECIMAL(15,2),
  status ENUM('belum bayar','lunas','dibatalkan'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  FOREIGN KEY (distribusi_id) REFERENCES distribusi_barang(id)
) ENGINE=InnoDB;

-- ============================
-- PEMBAYARAN
-- ============================
CREATE TABLE pembayaran (
  id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
  id_invoice INT,
  metode VARCHAR(50),
  jumlah DECIMAL(15,2),
  tanggal_bayar DATETIME,
  bukti_transfer VARCHAR(255),
  status ENUM('pending','berhasil','gagal'),
  FOREIGN KEY (id_invoice) REFERENCES invoice(id_invoice)
) ENGINE=InnoDB;

-- ============================
-- NOTIFIKASI
-- ============================
CREATE TABLE notifikasi (
  id INT AUTO_INCREMENT PRIMARY KEY,

  receiver_id INT NOT NULL,
  sender_id INT DEFAULT NULL,
  permintaan_id INT DEFAULT NULL,

  pesan TEXT NOT NULL,
  pesan_customer TEXT NULL,
  status_baca TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (receiver_id) REFERENCES users(id)
    ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE SET NULL,
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;
