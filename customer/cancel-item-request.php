<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id <= 0 || !isset($_POST['permintaan_id'])) {
  header("Location: history-item-request.php");
  exit;
}

$permintaan_id = (int) $_POST['permintaan_id'];

/* ============================
   VALIDASI PERMINTAAN
============================ */
$check = $conn->prepare("
  SELECT 
    id,
    status,
    kode_permintaan
  FROM permintaan_barang
  WHERE id = ?
    AND user_id = ?
    AND deleted_at IS NULL
");
$check->bind_param("ii", $permintaan_id, $user_id);
$check->execute();
$data = $check->get_result()->fetch_assoc();

if (!$data) {
  header("Location: history-item-request.php?error=permintaan_not_found");
  exit;
}

if ($data['status'] !== 'diajukan') {
  header("Location: history-item-request.php?error=permintaan_cannot_cancel");
  exit;
}

/* ============================
   UPDATE STATUS → dibatalkan
============================ */
$update = $conn->prepare("
  UPDATE permintaan_barang
  SET status = 'dibatalkan'
  WHERE id = ?
");
$update->bind_param("i", $permintaan_id);
$update->execute();

/* ============================
   NOTIFIKASI (DISESUAIKAN)
============================ */

/* Notifikasi untuk ADMIN / SISTEM */
$pesan_admin =
  "Permintaan barang dibatalkan oleh customer.\n\n" .
  "Kode Permintaan: {$data['kode_permintaan']}\n" .
  "Status: Diajukan → Dibatalkan";

/* Notifikasi untuk CUSTOMER */
$pesan_customer =
  "Permintaan barang Anda berhasil dibatalkan.\n\n" .
  "Kode Permintaan: {$data['kode_permintaan']}\n" .
  "Status: Dibatalkan";

/*
  receiver  : CUSTOMER (arsip notifikasi user)
  sender    : CUSTOMER (self)
*/
insertNotifikasi(
  $conn,
  $user_id,
  $user_id,
  $permintaan_id,
  $pesan_admin,
  $pesan_customer
);

/* ============================
   REDIRECT
============================ */
header("Location: history-item-request.php?success=cancel_item_request");
exit;
