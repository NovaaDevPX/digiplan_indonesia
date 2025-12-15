<?php
require '../include/conn.php';
require '../include/auth.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

/* VALIDASI: pastikan distribusi milik customer */
$cek = mysqli_query($conn, "
  SELECT d.id
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  WHERE d.id = $id
    AND p.user_id = $user_id
    AND d.status_distribusi = 'dikirim'
");

if (mysqli_num_rows($cek) === 0) {
  die('Akses tidak valid');
}

/* UPDATE STATUS */
mysqli_query($conn, "
  UPDATE distribusi_barang
  SET status_distribusi = 'diterima',
      tanggal_terima = NOW()
  WHERE id = $id
");

/* REDIRECT */
header("Location: history-item-request.php?success=diterima");
exit;
