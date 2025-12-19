<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement-v2.php');
  exit;
}

$admin_id      = (int) $_POST['admin_id'];
$permintaan_id = (int) $_POST['permintaan_id'];
$jumlah        = (int) $_POST['jumlah'];
$harga_satuan  = (float) $_POST['harga_satuan'];

$supplier = trim($_POST['supplier']);
$kontak   = trim($_POST['kontak_supplier']);
$alamat   = trim($_POST['alamat_supplier']);

if (
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

/* VALIDASI PERMINTAAN */
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

$harga_total = $jumlah * $harga_satuan;
$kode = 'PGD-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

$conn->begin_transaction();

try {

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

  $up = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ?
  ");
  $up->bind_param("i", $permintaan_id);
  $up->execute();

  $conn->commit();
  header('Location: procurement-v2.php?success=item_procurement_success');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
