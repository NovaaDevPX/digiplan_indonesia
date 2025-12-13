DROP DATABASE IF EXISTS digiplan_indonesia;
CREATE DATABASE digiplan_indonesia;
USE digiplan_indonesia;

-- ============================
-- roles
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
-- users
-- ============================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

INSERT INTO users VALUES
(1,'Super Admin','superadmin@gmail.com',MD5('123'),3,NOW()),
(2,'Admin Gudang','admin@gmail.com',MD5('123'),2,NOW()),
(3,'PT Maju Jaya','customer1@gmail.com',MD5('123'),1,NOW()),
(4,'CV Sukses Abadi','customer2@gmail.com',MD5('123'),1,NOW()),
(5,'PT Sejahtera','customer3@gmail.com',MD5('123'),1,NOW());

-- ============================
-- produk
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
(1,'Tumbler',750000,'1764647749_daun.webp','Tumbler Hydro Flask 21oz Standar Flex Cap',NOW());

-- ============================
-- barang
-- ============================
CREATE TABLE barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_barang VARCHAR(100),
  merk VARCHAR(100),
  warna VARCHAR(50),
  deskripsi TEXT,
  stok INT,
  harga DECIMAL(15,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO barang VALUES
(1,'Laptop','Lenovo','Hitam','Laptop kantor',50,15000000,NOW()),
(2,'Printer','Epson','Hitam','Printer Inkjet',30,2500000,NOW()),
(3,'TV','Samsung','Hitam','Smart TV 43 Inch',20,6500000,NOW()),
(4,'Tumbler','Hydro Flask','Pink','Tumbler 600ml',100,750000,NOW());

-- ============================
-- permintaan_barang
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
    'diajukan',
    'ditolak',
    'disetujui',
    'dalam_pengadaan',
    'siap_distribusi',
    'selesai'
  ),
  admin_id INT,
  tanggal_verifikasi DATETIME,
  catatan_admin TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO permintaan_barang VALUES
(1,'PRM-001',3,'Laptop','Lenovo','Hitam',10,'disetujui',1,NOW(),'Disetujui',NOW()),
(2,'PRM-002',4,'Printer','Epson','Hitam',5,'dalam_pengadaan',1,NOW(),'Diproses',NOW()),
(3,'PRM-003',5,'TV','Samsung','Hitam',3,'siap_distribusi',1,NOW(),'Siap kirim',NOW()),
(4,'PRM-004',3,'Tumbler','Hydro Flask','Pink',20,'selesai',1,NOW(),'Selesai',NOW());

-- ============================
-- pengadaan_barang (UPDATED)
-- ============================
CREATE TABLE pengadaan_barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_pengadaan VARCHAR(50) UNIQUE,
  permintaan_id INT,
  admin_id INT,
  barang_id INT,
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
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id),
  FOREIGN KEY (admin_id) REFERENCES users(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
) ENGINE=InnoDB;

INSERT INTO pengadaan_barang VALUES
(1,'PGD-001',1,2,1,'Laptop','Lenovo','Hitam',10,'PT Lenovo Indonesia','021-555111','Jakarta Selatan',14500000,145000000,'selesai','2025-01-10',NOW()),
(2,'PGD-002',2,2,2,'Printer','Epson','Hitam',5,'PT Epson Indonesia','021-666222','Jakarta Barat',2400000,12000000,'diproses','2025-01-12',NOW()),
(3,'PGD-003',3,2,3,'TV','Samsung','Hitam',3,'PT Samsung','021-777333','Jakarta Pusat',6300000,18900000,'selesai','2025-01-15',NOW());

-- ============================
-- distribusi_barang
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
  status_distribusi ENUM('siap_dikirim','dikirim','diterima'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pengadaan_id) REFERENCES pengadaan_barang(id),
  FOREIGN KEY (permintaan_id) REFERENCES permintaan_barang(id),
  FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO distribusi_barang VALUES
(1,'DST-001',1,1,2,'Jakarta Selatan','JNE','JNE001','2025-01-18','2025-01-20','diterima',NOW()),
(2,'DST-002',3,3,2,'Bandung','SiCepat','SCP002','2025-01-19',NULL,'dikirim',NOW());

-- ============================
-- invoice
-- ============================
CREATE TABLE invoice (
  id_invoice INT AUTO_INCREMENT PRIMARY KEY,
  distribusi_id INT,
  nomor_invoice VARCHAR(50) UNIQUE,
  tanggal_invoice DATE,
  jatuh_tempo DATE,
  total DECIMAL(15,2),
  status ENUM('belum bayar','lunas'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (distribusi_id) REFERENCES distribusi_barang(id)
) ENGINE=InnoDB;

INSERT INTO invoice VALUES
(1,1,'INV-001','2025-01-21','2025-01-30',145000000,'lunas',NOW()),
(2,2,'INV-002','2025-01-22','2025-01-31',18900000,'belum bayar',NOW());

-- ============================
-- pembayaran
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
(1,1,'Transfer Bank',145000000,'2025-01-22 10:30:00','bukti1.jpg','berhasil');

-- ============================
-- notifikasi
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
(1,3,1,'Permintaan Laptop Lenovo telah disetujui',0,NOW()),
(2,3,1,'Laptop Lenovo telah dikirim',0,NOW()),
(3,4,2,'Permintaan Printer Epson sedang diproses',0,NOW());

-- ============================
-- chat
-- ============================
CREATE TABLE chat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengirim_id INT,
  penerima_id INT,
  pesan TEXT,
  tipe ENUM('text','image','audio','system'),
  lampiran VARCHAR(255),
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  dibaca TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO chat VALUES
(1,3,1,'Apakah Laptop Lenovo sudah dikirim?','text',NULL,NOW(),1),
(2,1,3,'Laptop Lenovo sudah dikirim hari ini','text',NULL,NOW(),0);
