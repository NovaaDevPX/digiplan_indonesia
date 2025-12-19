<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

/* =========================
   DATA LOGIN (PEMBUAT NOTIFIKASI)
========================= */
$admin_id_login = (int) $_SESSION['user_id']; // PEMBUAT NOTIFIKASI

/* =========================
   DATA FORM
========================= */
$admin_id      = (int) $_POST['admin_id'];
$permintaan_id = (int) $_POST['permintaan_id'];
$jumlah        = (int) $_POST['jumlah'];
$harga_satuan  = (float) $_POST['harga_satuan'];

$supplier = trim($_POST['supplier']);
$kontak   = trim($_POST['kontak_supplier']);
$alamat   = trim($_POST['alamat_supplier']);

/* =========================
   VALIDASI DASAR
========================= */
if (
  $admin_id_login <= 0 ||
  $admin_id <= 0 ||
  $permintaan_id <= 0 ||
  $jumlah <= 0 ||
  $harga_satuan <= 0 ||
  $supplier === '' ||
  $kontak === '' ||
  $alamat === ''
) {
  die('❌ Data tidak valid');
}

/* =========================
   VALIDASI PERMINTAAN
========================= */
$q = $conn->prepare("
  SELECT jumlah, nama_barang, merk, warna
  FROM permintaan_barang
  WHERE id = ? AND status = 'disetujui'
");
$q->bind_param("i", $permintaan_id);
$q->execute();
$permintaan = $q->get_result()->fetch_assoc();

if (!$permintaan) {
  die('❌ Permintaan tidak valid');
}

if ($jumlah < $permintaan['jumlah']) {
  die('❌ Jumlah pengadaan tidak boleh kurang dari permintaan');
}

/* =========================
   HITUNG & KODE
========================= */
$harga_total = $jumlah * $harga_satuan;

// ambil nomor urut
$r = $conn->query("SELECT COUNT(*) total FROM pengadaan_barang");
$total = $r->fetch_assoc()['total'] + 1;
$kode = 'PGD-' . str_pad($total, 3, '0', STR_PAD_LEFT);

$conn->begin_transaction();

try {

  /* =========================
     INSERT PENGADAAN
  ========================= */
  $stmt = $conn->prepare("
    INSERT INTO pengadaan_barang (
      kode_pengadaan,
      permintaan_id,
      admin_id,
      barang_id,
      nama_barang,
      merk,
      warna,
      jumlah,
      supplier,
      kontak_supplier,
      alamat_supplier,
      harga_satuan,
      harga_total,
      status_pengadaan,
      tanggal_pengadaan
    ) VALUES (
      ?, ?, ?, NULL,
      ?, ?, ?, ?,
      ?, ?, ?,
      ?, ?,
      'diproses',
      CURDATE()
    )
  ");

  $stmt->bind_param(
    "siisssisssdd",
    $kode,
    $permintaan_id,
    $admin_id,
    $permintaan['nama_barang'],
    $permintaan['merk'],
    $permintaan['warna'],
    $jumlah,
    $supplier,
    $kontak,
    $alamat,
    $harga_satuan,
    $harga_total
  );

  $stmt->execute();

  /* =========================
     UPDATE STATUS PERMINTAAN
  ========================= */
  $up = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ?
  ");
  $up->bind_param("i", $permintaan_id);
  $up->execute();

  /* =========================
     NOTIFIKASI (VERSI V2)
     PEMBUAT = ADMIN LOGIN
  ========================= */
  $pesan =
    "Super Admin membuat pengadaan baru\n" .
    "Kode Pengadaan: $kode\n" .
    "Barang: {$permintaan['nama_barang']}\n" .
    "Merk: {$permintaan['merk']}\n" .
    "Warna: {$permintaan['warna']}\n" .
    "Jumlah: $jumlah\n" .
    "Harga Satuan: " . number_format($harga_satuan, 0, ',', '.') . "\n" .
    "Total Harga: " . number_format($harga_total, 0, ',', '.') . "\n" .
    "Supplier: $supplier\n" .
    "Kontak Supplier: $kontak\n" .
    "Alamat Supplier: $alamat\n" .
    "Status: Disetujui → Dalam Pengadaan";

  insertNotifikasiDB(
    $conn,
    $admin_id_login, // ✅ PEMBUAT NOTIFIKASI (ADMIN LOGIN)
    $permintaan_id,
    $pesan
  );

  /* =========================
     COMMIT
  ========================= */
  $conn->commit();

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
