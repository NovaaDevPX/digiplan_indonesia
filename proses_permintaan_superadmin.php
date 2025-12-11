<?php
require 'function.php';
require 'cek.php';
cek_role(['super_admin']);

$id = $_GET['id'];
$aksi = $_GET['aksi'];

// Ambil data user dari permintaan
$q = mysqli_query($conn, "SELECT user_id FROM permintaan_barang WHERE id='$id'");
$data = mysqli_fetch_assoc($q);
$user_id = $data['user_id'];

if ($aksi == 'terima') {
    mysqli_query($conn, "UPDATE permintaan_barang SET status='Diterima' WHERE id='$id'");
}

// Kirim notifikasi ke customer
tambahNotifikasi($row['user_id'], $id, "Permintaan barang Anda telah diputuskan oleh Super Admin ($aksi).");

// Kirim notifikasi ke admin, kalau ada
if (!empty($row['admin_id'])) {
    tambahNotifikasi($row['admin_id'], $id, "Super Admin telah memutuskan status permintaan barang.");
}

header("Location: permintaan_barang_superadmin.php");
exit;
?>
