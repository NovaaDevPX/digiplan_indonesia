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
-- PRODUK
-- ============================
CREATE TABLE produk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(255),
  harga INT,
  gambar VARCHAR(255),
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO produk VALUES
(1,'Laptop Kantor',15000000,'laptop.webp','Laptop operasional kantor',NOW(),NULL),
(2,'Printer Inkjet',2500000,'printer.webp','Printer administrasi',NOW(),NULL),
(3,'Smart TV 43 Inch',6500000,'tv.webp','TV ruang meeting',NOW(),NULL),
(4,'Tumbler Premium',750000,'tumbler.webp','Tumbler stainless premium',NOW(),NULL);

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
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO barang VALUES
(1,'Laptop','Lenovo','Hitam','Laptop ThinkPad kantor',40,14800000,NOW(),NULL),
(2,'Printer','Epson','Hitam','Printer Epson L-Series',25,2450000,NOW(),NULL),
(3,'TV','Samsung','Hitam','Smart TV UHD 43 Inch',15,6400000,NOW(),NULL),
(4,'Tumbler','Hydro Flask','Pink','Tumbler 600ml',80,730000,NOW(),NULL);

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

INSERT INTO permintaan_barang VALUES
(1,'PRM-2025-001',3,'Laptop','Lenovo','Hitam',8,'selesai',2,'2025-01-05 10:00:00','Disetujui & selesai',NOW(),NULL),
(2,'PRM-2025-002',4,'Printer','Epson','Hitam',5,'selesai',2,'2025-01-07 11:00:00','Pengadaan selesai',NOW(),NULL),
(3,'PRM-2025-003',5,'TV','Samsung','Hitam',2,'siap_distribusi',2,'2025-01-10 14:00:00','Siap dikirim',NOW(),NULL),
(4,'PRM-2025-004',3,'Tumbler','Hydro Flask','Pink',15,'disetujui',2,'2025-01-12 09:00:00','Menunggu pengadaan',NOW(),NULL);

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


INSERT INTO pengadaan_barang VALUES
(1,'PGD-2025-001',1,2,1,'Laptop','Lenovo','Hitam',8,'PT Lenovo Indonesia','021-555111','Jakarta Selatan',14500000,116000000,'selesai','2025-01-06',NOW(),NULL),
(2,'PGD-2025-002',2,2,2,'Printer','Epson','Hitam',5,'PT Epson Indonesia','021-666222','Jakarta Barat',2400000,12000000,'selesai','2025-01-08',NOW(),NULL),
(3,'PGD-2025-003',3,2,3,'TV','Samsung','Hitam',2,'PT Samsung Indonesia','021-777333','Jakarta Pusat',6300000,12600000,'selesai','2025-01-11',NOW(),NULL);

-- ============================
-- DISTRIBUSI BARANG
-- ============================
CREATE TABLE distribusi_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_distribusi VARCHAR(50) UNIQUE,
  pengadaan_id INT,
  permintaan_id INT,
  admin_id INT,
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

INSERT INTO distribusi_barang VALUES
(1,'DST-2025-001',1,1,2,'Jl. Sudirman Jakarta','JNE','JNE-889900','2025-01-09','2025-01-11','diterima',NOW(),NULL),
(2,'DST-2025-002',2,2,2,'Jl. Asia Afrika Bandung','SiCepat','SCP-112233','2025-01-10','2025-01-12','diterima',NOW(),NULL),
(3,'DST-2025-003',3,3,2,'Jl. Diponegoro Surabaya','J&T','JNT-445566','2025-01-13',NULL,'dikirim',NOW(),NULL);

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

INSERT INTO invoice VALUES
(1,1,'INV-2025-001','2025-01-12','2025-01-22',116000000,'lunas',NOW(),NULL),
(2,2,'INV-2025-002','2025-01-13','2025-01-23',12000000,'lunas',NOW(),NULL),
(3,3,'INV-2025-003','2025-01-14','2025-01-24',12600000,'belum bayar',NOW(),NULL);

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

INSERT INTO pembayaran VALUES
(1,1,'Transfer Bank',116000000,'2025-01-13 09:15:00','bukti_inv_001.jpg','berhasil'),
(2,2,'Transfer Bank',12000000,'2025-01-14 10:45:00','bukti_inv_002.jpg','berhasil');

-- ============================
-- NOTIFIKASI
-- ============================
CREATE TABLE notifikasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  permintaan_id INT,
  pesan TEXT,
  status_baca TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id)
) ENGINE=InnoDB;

INSERT INTO notifikasi VALUES
(1,3,1,'Invoice INV-2025-001 telah lunas',1,NOW()),
(2,4,2,'Invoice INV-2025-002 telah lunas',1,NOW()),
(3,5,3,'Invoice INV-2025-003 menunggu pembayaran',0,NOW());
