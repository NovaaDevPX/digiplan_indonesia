<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

if (!isset($_GET['id'])) {
  header("Location: procurement.php?error=invalid");
  exit;
}

$pengadaan_id = (int) $_GET['id'];

/* Ambil data pengadaan */
$q = $conn->query("
  SELECT id, permintaan_id, status_pengadaan 
  FROM pengadaan_barang 
  WHERE id = $pengadaan_id
");

if ($q->num_rows === 0) {
  header("Location: procurement.php?error=notfound");
  exit;
}

$data = $q->fetch_assoc();

/* Validasi status */
if ($data['status_pengadaan'] !== 'diproses') {
  header("Location: procurement.php?error=invalidstatus");
  exit;
}

$permintaan_id = $data['permintaan_id'];

/* TRANSAKSI (AMAN) */
$conn->begin_transaction();

try {
  // Update pengadaan
  $conn->query("
    UPDATE pengadaan_barang 
    SET status_pengadaan = 'selesai' 
    WHERE id = $pengadaan_id
  ");

  // Update permintaan
  $conn->query("
    UPDATE permintaan_barang 
    SET status = 'siap_distribusi' 
    WHERE id = $permintaan_id
  ");

  $conn->commit();

  header("Location: procurement.php?success=barang_masuk");
  exit;
} catch (Exception $e) {
  $conn->rollback();
  header("Location: procurement.php?error=failed");
  exit;
}
