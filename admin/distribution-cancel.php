<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

if (!isset($_GET['id'])) {
  header("Location: distribution.php");
  exit;
}

$id = (int) $_GET['id'];

// mulai transaction
mysqli_begin_transaction($conn);

try {

  /* ambil data distribusi */
  $q = mysqli_query($conn, "
    SELECT id, permintaan_id, status_distribusi
    FROM distribusi_barang
    WHERE id = $id
      AND deleted_at IS NULL
    LIMIT 1
  ");

  if (!$q || mysqli_num_rows($q) === 0) {
    throw new Exception('Data distribusi tidak ditemukan');
  }

  $data = mysqli_fetch_assoc($q);

  // hanya boleh dibatalkan jika status dikirim
  if ($data['status_distribusi'] !== 'dikirim') {
    throw new Exception('Distribusi tidak dapat dibatalkan');
  }

  /* 1. update distribusi (soft delete + status) */
  $updateDistribusi = mysqli_query($conn, "
    UPDATE distribusi_barang
    SET status_distribusi = 'dibatalkan',
        deleted_at = NOW()
    WHERE id = $id
  ");

  if (!$updateDistribusi) {
    throw new Exception('Gagal update distribusi');
  }

  /* 2. kembalikan status permintaan */
  $updatePermintaan = mysqli_query($conn, "
    UPDATE permintaan_barang
    SET status = 'siap_distribusi'
    WHERE id = {$data['permintaan_id']}
  ");

  if (!$updatePermintaan) {
    throw new Exception('Gagal update permintaan');
  }

  // commit jika semua sukses
  mysqli_commit($conn);

  header("Location: distribution.php?cancel=success");
  exit;
} catch (Exception $e) {

  mysqli_rollback($conn);

  header("Location: distribution.php?cancel=failed");
  exit;
}
