<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

$admin  = $_SESSION['user_id'];
$id     = (int) $_POST['id'];
$alamat = $_POST['alamat'];
$kurir  = $_POST['kurir'];
$status = $_POST['status'];

/* Ambil data lama */
$qOld = mysqli_query($conn, "
  SELECT d.kode_distribusi, d.alamat_pengiriman, d.kurir, d.status_distribusi, d.permintaan_id
  FROM distribusi_barang d
  WHERE d.id = '$id'
");
$old = mysqli_fetch_assoc($qOld);

/* Update */
mysqli_query($conn, "
  UPDATE distribusi_barang SET
    alamat_pengiriman = '$alamat',
    kurir = '$kurir',
    status_distribusi = '$status',
    tanggal_terima = IF('$status'='diterima', CURDATE(), tanggal_terima)
  WHERE id = '$id'
");

/* Deteksi perubahan */
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

if ($changes) {
  $pesan =
    "Super Admin memperbarui distribusi {$old['kode_distribusi']}\n" .
    implode("\n", $changes);

  insertNotifikasiDB(
    $conn,
    $admin,
    $old['permintaan_id'],
    $pesan
  );
}

header("Location: distribution.php");
exit;
