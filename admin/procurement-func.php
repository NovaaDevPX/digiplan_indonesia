<?php
require '../include/conn.php';
require '../include/auth.php';
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

$merk          = $_POST['merk'] ?? null;
$warna         = $_POST['warna'] ?? null;
$supplier      = $_POST['nama_supplier'] ?? null;
$kontak        = $_POST['kontak'] ?? null;
$alamat        = $_POST['alamat'] ?? null;
$tanggal       = $_POST['tanggal'] ?? date('Y-m-d');

/* =====================
   VALIDASI DASAR
===================== */
if (!$permintaan_id || !$barang_id || $jumlah <= 0 || $harga_total <= 0) {
  die('Data tidak valid');
}

$harga_satuan = $harga_total / $jumlah;
$status_pengadaan = 'diproses';

/* =====================
   TRANSACTION
===================== */
$conn->begin_transaction();

try {

  /* =====================
     1️⃣ GENERATE KODE PENGADAAN
     FORMAT: PGD-001
  ===================== */
  $qKode = $conn->query("
    SELECT kode_pengadaan 
    FROM pengadaan_barang 
    ORDER BY id DESC 
    LIMIT 1
  ");

  if ($qKode->num_rows > 0) {
    $lastKode = $qKode->fetch_assoc()['kode_pengadaan'];
    $lastNumber = (int) substr($lastKode, 4); // ambil angka setelah PGD-
    $newNumber = $lastNumber + 1;
  } else {
    $newNumber = 1;
  }

  $kode_pengadaan = 'PGD-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

  /* =====================
     2️⃣ AMBIL NAMA BARANG
  ===================== */
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

  /* =====================
     3️⃣ INSERT PENGADAAN
  ===================== */
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
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  ");

  if (!$stmt) {
    throw new Exception($conn->error);
  }

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

  /* =====================
     4️⃣ UPDATE STOK BARANG
  ===================== */
  $stok = $conn->prepare("
    UPDATE barang
    SET stok = stok + ?
    WHERE id = ?
  ");
  $stok->bind_param("ii", $jumlah, $barang_id);
  $stok->execute();

  /* =====================
     5️⃣ UPDATE STATUS PERMINTAAN
  ===================== */
  $update = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ?
      AND status = 'disetujui'
  ");
  $update->bind_param("i", $permintaan_id);
  $update->execute();

  if ($update->affected_rows === 0) {
    throw new Exception('Status permintaan tidak valid atau sudah diproses');
  }

  /* =====================
     COMMIT
  ===================== */
  $conn->commit();

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Exception $e) {
  $conn->rollback();
  die('ERROR: ' . $e->getMessage());
}
