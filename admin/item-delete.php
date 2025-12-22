<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

$admin_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
  header("Location: item.php");
  exit;
}

$id = (int) $_GET['id'];

/* =========================
   AMBIL DATA BARANG
========================= */
$qBarang = $conn->prepare("
  SELECT nama_barang
  FROM barang
  WHERE id = ?
  LIMIT 1
");
$qBarang->bind_param("i", $id);
$qBarang->execute();
$barang = $qBarang->get_result()->fetch_assoc();
$qBarang->close();

if (!$barang) {
  header("Location: item.php?error=itemnotfound");
  exit;
}

$nama_barang = $barang['nama_barang'];

/* =========================
   DELETE BARANG
========================= */
$del = $conn->prepare("DELETE FROM barang WHERE id = ?");
$del->bind_param("i", $id);

if (!$del->execute()) {
  header("Location: item.php?error=delete_failed");
  exit;
}

/* =========================
   PESAN NOTIFIKASI (ADMIN)
========================= */
$pesan_admin =
  "Data barang dihapus oleh Admin\n\n" .
  "Nama Barang : $nama_barang\n" .
  "ID Barang   : $id";

/* =========================
   KIRIM KE SEMUA ADMIN
========================= */
$qAdmin = $conn->query("
  SELECT id
  FROM users
  WHERE role_id = 2
    AND deleted_at IS NULL
");

while ($admin = $qAdmin->fetch_assoc()) {
  insertNotifikasi(
    $conn,
    (int) $admin['id'], // RECEIVER ADMIN
    $admin_id,     // SENDER  ADMIN
    null,               // tidak terkait permintaan
    $pesan_admin,       // pesan admin
    null                // tidak ada pesan customer
  );
}

header("Location: item.php?success=item_deleted");
exit;
