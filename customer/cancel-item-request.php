<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'];

if (!isset($_POST['permintaan_id'])) {
  header("Location: history-item-request.php");
  exit;
}

$permintaan_id = intval($_POST['permintaan_id']);

/* ============================
   VALIDASI PERMINTAAN
============================ */
$check = $conn->prepare("
  SELECT id, status 
  FROM permintaan_barang
  WHERE id = ?
    AND user_id = ?
    AND deleted_at IS NULL
");
$check->bind_param("ii", $permintaan_id, $user_id);
$check->execute();
$data = $check->get_result()->fetch_assoc();

if (!$data) {
  $_SESSION['error'] = "Permintaan tidak ditemukan.";
  header("Location: history-item-request.php");
  exit;
}

if ($data['status'] !== 'diajukan') {
  $_SESSION['error'] = "Permintaan tidak dapat dibatalkan.";
  header("Location: history-item-request.php");
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
   NOTIFIKASI USER
============================ */
$notif = $conn->prepare("
  INSERT INTO notifikasi (user_id, permintaan_id, pesan)
  VALUES (?, ?, ?)
");
$pesan = "Permintaan barang berhasil dibatalkan oleh Customer.";
$notif->bind_param("iis", $user_id, $permintaan_id, $pesan);
$notif->execute();

$_SESSION['success'] = "Permintaan berhasil dibatalkan.";
header("Location: history-item-request.php?success=cancel_item_request");
exit;
