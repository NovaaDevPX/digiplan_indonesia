<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

$id      = (int) $_POST['id'];
$supplier = trim($_POST['supplier']);
$kontak   = trim($_POST['kontak_supplier']);
$alamat   = trim($_POST['alamat_supplier']);

if (!$id || !$supplier || !$kontak || !$alamat) {
  die('Data tidak lengkap');
}

/* pastikan bukan dibatalkan */
$check = $conn->prepare("
  SELECT status_pengadaan 
  FROM pengadaan_barang 
  WHERE id = ?
");
$check->bind_param("i", $id);
$check->execute();
$status = $check->get_result()->fetch_assoc();

if (!$status || $status['status_pengadaan'] === 'dibatalkan') {
  die('Pengadaan tidak valid');
}

/* update */
$update = $conn->prepare("
  UPDATE pengadaan_barang
  SET supplier = ?, kontak_supplier = ?, alamat_supplier = ?
  WHERE id = ?
");

$update->bind_param("sssi", $supplier, $kontak, $alamat, $id);
$update->execute();

header('Location: procurement.php?success=update');
exit;
