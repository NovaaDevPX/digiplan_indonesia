<?php
require 'function.php';
require 'cek.php';
cek_role(['admin']);

if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    header('Location: permintaan_barang_admin.php');
    exit;
}

$id = intval($_GET['id']);
$aksi = $_GET['aksi'];
$admin_id = $_SESSION['user_id'];

if ($aksi === 'ajukan') {
    $status = 'Menunggu Super Admin';
    $catatan = NULL;
} elseif ($aksi === 'tolak') {
    $status = 'Ditolak';
    $catatan = 'Ditolak oleh admin';
} else {
    header('Location: permintaan_barang_admin.php');
    exit;
}

$sql = "UPDATE permintaan_barang 
        SET status='$status', admin_id=$admin_id, tanggal_verifikasi=NOW(), catatan_admin='$catatan' 
        WHERE id=$id";

if ($conn->query($sql)) {
    header('Location: permintaan_barang_admin.php?msg=success');
} else {
    die("Gagal memperbarui: " . $conn->error);
}
?>
