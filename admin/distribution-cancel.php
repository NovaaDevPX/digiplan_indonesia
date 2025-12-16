<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

if (!isset($_GET['id'])) {
  header("Location: distribution.php");
  exit;
}

$admin = $_SESSION['user_id'];
$id = (int) $_GET['id'];

mysqli_begin_transaction($conn);

try {

  $q = mysqli_query($conn, "
    SELECT id, kode_distribusi, permintaan_id, status_distribusi
    FROM distribusi_barang
    WHERE id = $id
      AND deleted_at IS NULL
  ");

  if (!$q || mysqli_num_rows($q) === 0) {
    throw new Exception('Distribusi tidak ditemukan');
  }

  $data = mysqli_fetch_assoc($q);

  if ($data['status_distribusi'] !== 'dikirim') {
    throw new Exception('Distribusi tidak dapat dibatalkan');
  }

  mysqli_query($conn, "
    UPDATE distribusi_barang
    SET status_distribusi = 'dibatalkan',
        deleted_at = NOW()
    WHERE id = $id
  ");

  mysqli_query($conn, "
    UPDATE permintaan_barang
    SET status = 'siap_distribusi'
    WHERE id = {$data['permintaan_id']}
  ");

  /* NOTIFIKASI */
  $pesan =
    "Admin membatalkan distribusi\n" .
    "Kode Distribusi: {$data['kode_distribusi']}\n" .
    "Status: Dikirim → Dibatalkan";

  insertNotifikasiDB(
    $conn,
    $admin,
    $data['permintaan_id'],
    $pesan
  );

  mysqli_commit($conn);

  header("Location: distribution.php?cancel=success");
  exit;
} catch (Exception $e) {
  mysqli_rollback($conn);
  header("Location: distribution.php?cancel=failed");
  exit;
}
