<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$admin_id = (int) $_SESSION['user_id'];

if (!isset($_POST['id'], $_POST['alamat'], $_POST['kurir'], $_POST['status'])) {
  header("Location: distribution.php");
  exit;
}

$distribusi_id = (int) $_POST['id'];
$alamat        = trim($_POST['alamat']);
$kurir         = trim($_POST['kurir']);
$status        = $_POST['status'];

/* =========================
   AMBIL DATA LAMA + CUSTOMER
========================= */
$qOld = $conn->prepare("
  SELECT 
    d.kode_distribusi,
    d.alamat_pengiriman,
    d.kurir,
    d.status_distribusi,
    d.permintaan_id,
    pb.user_id AS customer_id,
    u.name     AS customer_name
  FROM distribusi_barang d
  JOIN permintaan_barang pb ON d.permintaan_id = pb.id
  JOIN users u ON pb.user_id = u.id
  WHERE d.id = ?
");
$qOld->bind_param("i", $distribusi_id);
$qOld->execute();
$old = $qOld->get_result()->fetch_assoc();
$qOld->close();

if (!$old) {
  header("Location: distribution.php?error=data_not_found");
  exit;
}

/* =========================
   UPDATE DISTRIBUSI
========================= */
$up = $conn->prepare("
  UPDATE distribusi_barang
  SET alamat_pengiriman = ?,
      kurir = ?,
      status_distribusi = ?,
      tanggal_terima = IF(? = 'diterima', CURDATE(), tanggal_terima)
  WHERE id = ?
");
$up->bind_param(
  "ssssi",
  $alamat,
  $kurir,
  $status,
  $status,
  $distribusi_id
);
$up->execute();
$up->close();

/* =========================
   DETEKSI PERUBAHAN
========================= */
$changes = [];

if ($old['alamat_pengiriman'] !== $alamat) {
  $changes[] = "Alamat: {$old['alamat_pengiriman']} → $alamat";
}
if ($old['kurir'] !== $kurir) {
  $changes[] = "Kurir: {$old['kurir']} → $kurir";
}
if ($old['status_distribusi'] !== $status) {
  $changes[] = "Status: {$old['status_distribusi']} → $status";
}

/* =========================
   NOTIFIKASI
========================= */
if (!empty($changes)) {

  /* ---------- PESAN ADMIN ---------- */
  $pesan_admin =
    "Distribusi diperbarui oleh Admin\n\n" .
    "Kode Distribusi: {$old['kode_distribusi']}\n\n" .
    "Perubahan:\n" .
    implode("\n", $changes);

  /* ---------- PESAN CUSTOMER ---------- */
  $pesan_customer =
    "Halo {$old['customer_name']},\n\n" .
    "Informasi pengiriman barang Anda telah diperbarui.\n\n" .
    "Kode Distribusi: {$old['kode_distribusi']}\n\n" .
    "Perubahan:\n" .
    implode("\n", $changes);

  /* ---------- KIRIM KE CUSTOMER ---------- */
  insertNotifikasi(
    $conn,
    $old['customer_id'],   // receiver customer
    $admin_id,        // sender  admin
    $old['permintaan_id'],
    $pesan_admin,
    $pesan_customer
  );

  /* ---------- LOG KE ADMIN ---------- */
  insertNotifikasi(
    $conn,
    $admin_id,
    $admin_id,
    $old['permintaan_id'],
    $pesan_admin,
    null
  );
}

header("Location: distribution.php?success=distribution_updated");
exit;
