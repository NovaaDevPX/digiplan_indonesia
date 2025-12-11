<?php
require 'function.php';
require 'cek.php';
cek_role(['customer']);

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];

$q = mysqli_query($conn, "SELECT nama_produk FROM produk WHERE id='$id'");
$p = mysqli_fetch_assoc($q);

if (!$p) die("Produk tidak ditemukan");

$nama = $p['nama_produk'];
$tanggal = date('Y-m-d H:i:s');

$insert = mysqli_query($conn, "
    INSERT INTO permintaan_barang (user_id, nama_barang, tanggal_permintaan, status)
    VALUES ('$user_id', '$nama', '$tanggal', 'proses')
");

header("Location: customer_dashboard.php?alert=success");
exit;
