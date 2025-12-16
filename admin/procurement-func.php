<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

/* =====================
   AMBIL DATA POST
===================== */
$admin_id      = (int) $_POST['admin_id'];
$permintaan_id = (int) $_POST['permintaan_id'];
$barang_id     = (int) $_POST['barang_id'];
$jumlah        = (int) $_POST['jumlah'];
$harga_total   = (float) $_POST['harga_total'];

$merk     = $_POST['merk'] ?? '-';
$warna    = $_POST['warna'] ?? '-';
$supplier = $_POST['nama_supplier'] ?? '-';
$kontak   = $_POST['kontak'] ?? '-';
$alamat   = $_POST['alamat'] ?? '-';
$tanggal  = $_POST['tanggal'] ?? date('Y-m-d');

if (!$permintaan_id || !$barang_id || $jumlah <= 0 || $harga_total <= 0) {
  die('Data tidak valid');
}

$harga_satuan     = $harga_total / $jumlah;
$status_pengadaan = 'diproses';

$conn->begin_transaction();

try {

  /* Generate kode pengadaan */
  $qKode = $conn->query("
    SELECT kode_pengadaan
    FROM pengadaan_barang
    ORDER BY id DESC
    LIMIT 1
  ");

  if ($qKode->num_rows > 0) {
    $lastKode = $qKode->fetch_assoc()['kode_pengadaan'];
    $newNumber = (int) substr($lastKode, 4) + 1;
  } else {
    $newNumber = 1;
  }

  $kode_pengadaan = 'PGD-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

  /* Ambil nama barang */
  $qBarang = $conn->prepare("
    SELECT nama_barang
    FROM barang
    WHERE id = ?
  ");
  $qBarang->bind_param("i", $barang_id);
  $qBarang->execute();
  $barang = $qBarang->get_result()->fetch_assoc();

  if (!$barang) {
    throw new Exception('Barang tidak ditemukan');
  }

  /* Insert pengadaan */
  $stmt = $conn->prepare("
    INSERT INTO pengadaan_barang (
      kode_pengadaan, permintaan_id, admin_id, barang_id,
      nama_barang, merk, warna, jumlah,
      supplier, kontak_supplier, alamat_supplier,
      harga_satuan, harga_total, status_pengadaan, tanggal_pengadaan
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  ");

  $stmt->bind_param(
    "siiisssisssddss",
    $kode_pengadaan,
    $permintaan_id,
    $admin_id,
    $barang_id,
    $barang['nama_barang'],
    $merk,
    $warna,
    $jumlah,
    $supplier,
    $kontak,
    $alamat,
    $harga_satuan,
    $harga_total,
    $status_pengadaan,
    $tanggal
  );
  $stmt->execute();

  /* Update stok */
  $stok = $conn->prepare("
    UPDATE barang
    SET stok = stok + ?
    WHERE id = ?
  ");
  $stok->bind_param("ii", $jumlah, $barang_id);
  $stok->execute();

  /* Update status permintaan */
  $update = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ? AND status = 'disetujui'
  ");
  $update->bind_param("i", $permintaan_id);
  $update->execute();

  if ($update->affected_rows === 0) {
    throw new Exception('Status permintaan tidak valid');
  }

  $conn->commit();

  /* =====================
     NOTIFIKASI LENGKAP
  ===================== */
  $pesan =
    "Admin membuat pengadaan baru\n" .
    "Kode Pengadaan: $kode_pengadaan\n" .
    "Barang: {$barang['nama_barang']}\n" .
    "Merk: $merk\n" .
    "Warna: $warna\n" .
    "Jumlah: $jumlah\n" .
    "Harga Satuan: " . number_format($harga_satuan, 0, ',', '.') . "\n" .
    "Total Harga: " . number_format($harga_total, 0, ',', '.') . "\n" .
    "Supplier: $supplier\n" .
    "Kontak Supplier: $kontak\n" .
    "Alamat Supplier: $alamat\n" .
    "Tanggal Pengadaan: $tanggal\n" .
    "Status: Disetujui → Dalam Pengadaan";

  insertNotifikasiDB(
    $conn,
    $admin_id,
    $permintaan_id,
    $pesan
  );

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Exception $e) {
  $conn->rollback();
  die('ERROR: ' . $e->getMessage());
}
