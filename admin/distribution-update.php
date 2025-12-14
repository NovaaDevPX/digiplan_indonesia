<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$id     = $_POST['id'];
$alamat = $_POST['alamat'];
$kurir  = $_POST['kurir'];
$status = $_POST['status'];

/* update distribusi */
mysqli_query($conn, "
UPDATE distribusi_barang SET
alamat_pengiriman = '$alamat',
kurir = '$kurir',
status_distribusi = '$status',
tanggal_terima = IF('$status'='diterima', CURDATE(), tanggal_terima)
WHERE id = '$id'
");

header("Location: distribution.php");
exit;
