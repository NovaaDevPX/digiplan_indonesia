<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

$id       = (int) $_POST['id'];
$supplier = trim($_POST['supplier']);
$kontak   = trim($_POST['kontak_supplier']);
$alamat   = trim($_POST['alamat_supplier']);

if (!$id || !$supplier || !$kontak || !$alamat) {
  die('Data tidak lengkap');
}

/* Ambil data lama + kode */
$oldQ = $conn->prepare("
  SELECT kode_pengadaan, supplier, kontak_supplier, alamat_supplier
  FROM pengadaan_barang
  WHERE id = ?
");
$oldQ->bind_param("i", $id);
$oldQ->execute();
$old = $oldQ->get_result()->fetch_assoc();

if (!$old) {
  die('Pengadaan tidak ditemukan');
}

/* Update */
$update = $conn->prepare("
  UPDATE pengadaan_barang
  SET supplier = ?, kontak_supplier = ?, alamat_supplier = ?
  WHERE id = ?
");
$update->bind_param("sssi", $supplier, $kontak, $alamat, $id);
$update->execute();

/* Perubahan */
$changes = [];

if ($old['supplier'] !== $supplier) {
  $changes[] = "Supplier: {$old['supplier']} → $supplier";
}
if ($old['kontak_supplier'] !== $kontak) {
  $changes[] = "Kontak: {$old['kontak_supplier']} → $kontak";
}
if ($old['alamat_supplier'] !== $alamat) {
  $changes[] = "Alamat: {$old['alamat_supplier']} → $alamat";
}

if (!empty($changes)) {
  $pesan =
    "Admin memperbarui pengadaan {$old['kode_pengadaan']}\n" .
    implode("\n", $changes);

  insertNotifikasiDB(
    $conn,
    $admin_id,
    null,
    $pesan
  );
}

header('Location: procurement.php?success=update');
exit;
