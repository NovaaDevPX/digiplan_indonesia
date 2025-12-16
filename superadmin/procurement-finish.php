<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

if (!isset($_GET['id'])) {
  header("Location: procurement.php?error=invalid");
  exit;
}

$pengadaan_id = (int) $_GET['id'];
$admin_id = $_SESSION['user_id'];

/* Ambil data pengadaan */
$q = $conn->query("
  SELECT id, permintaan_id, status_pengadaan, kode_pengadaan
  FROM pengadaan_barang
  WHERE id = $pengadaan_id
");

if ($q->num_rows === 0) {
  header("Location: procurement.php?error=notfound");
  exit;
}

$data = $q->fetch_assoc();

if ($data['status_pengadaan'] !== 'diproses') {
  header("Location: procurement.php?error=invalidstatus");
  exit;
}

$permintaan_id = $data['permintaan_id'];
$kode_pengadaan = $data['kode_pengadaan'];

$conn->begin_transaction();

try {

  $conn->query("
    UPDATE pengadaan_barang
    SET status_pengadaan = 'selesai'
    WHERE id = $pengadaan_id
  ");

  $conn->query("
    UPDATE permintaan_barang
    SET status = 'siap_distribusi'
    WHERE id = $permintaan_id
  ");

  $conn->commit();

  /* Notifikasi */
  $pesan =
    "Super Admin menyelesaikan pengadaan\n" .
    "Kode: $kode_pengadaan\n" .
    "Status: Diproses → Selesai";

  insertNotifikasiDB(
    $conn,
    $admin_id,
    $permintaan_id,
    $pesan
  );

  header("Location: procurement.php?success=barang_masuk");
  exit;
} catch (Exception $e) {
  $conn->rollback();
  header("Location: procurement.php?error=failed");
  exit;
}
