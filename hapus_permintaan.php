<?php
session_start();
require 'function.php';
require 'cek.php';

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$q = $conn->prepare("DELETE FROM permintaan_barang WHERE id=? AND user_id=? AND status='Proses'");
$q->bind_param("ii", $id, $user_id);
$q->execute();

echo "<script>alert('Data berhasil dihapus!');window.location='riwayat_permintaan.php';</script>";
?>
