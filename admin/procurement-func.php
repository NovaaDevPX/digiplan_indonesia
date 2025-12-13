<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

// =====================
// AMBIL DATA POST
// =====================
$admin_id       = $_POST['admin_id'];
$permintaan_id = $_POST['permintaan_id'];
$barang_id     = $_POST['barang_id'];
$jumlah         = $_POST['jumlah'];
$harga_total    = $_POST['harga_total'];
$merk           = $_POST['merk'] ?? null;
$warna          = $_POST['warna'] ?? null;
$nama           = $_POST['nama_supplier'] ?? null;
$kontak         = $_POST['kontak'] ?? null;
$alamat         = $_POST['alamat'] ?? null;
$deskripsi      = $_POST['deskripsi_barang'] ?? null;
$tanggal        = $_POST['tanggal'] ?? date('Y-m-d');

$conn->begin_transaction();

try {

  // =====================
  // 1️⃣ INSERT PENGADAAN
  // =====================
  $stmt = $conn->prepare("
    INSERT INTO pengadaan_barang
    (admin_id, barang_id, permintaan_id, jumlah, harga_total, merk, warna, deskripsi_barang, nama, kontak, alamat, tanggal)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");

  if (!$stmt) {
    throw new Exception($conn->error);
  }

  $stmt->bind_param(
    "iiiissssssss",
    $admin_id,
    $barang_id,
    $permintaan_id,
    $jumlah,
    $harga_total,
    $merk,
    $warna,
    $deskripsi,
    $nama,
    $kontak,
    $alamat,
    $tanggal
  );

  $stmt->execute();

  // =====================
  // 2️⃣ UPDATE STOK BARANG
  // =====================
  $stok = $conn->prepare("
    UPDATE barang 
    SET stok = stok + ? 
    WHERE id = ?
  ");
  $stok->bind_param("ii", $jumlah, $barang_id);
  $stok->execute();

  $conn->commit();

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Exception $e) {
  $conn->rollback();
  die("ERROR SQL: " . $e->getMessage());
}
