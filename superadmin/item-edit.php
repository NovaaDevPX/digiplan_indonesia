<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

$admin_id = $_SESSION['user_id'];

if (!isset($_POST['edit_barang'])) {
  header('Location: item.php');
  exit;
}

$id = (int) $_POST['id'];

/**
 * =========================
 * DATA SEBELUM UPDATE
 * =========================
 */
$qOld = $conn->prepare("
  SELECT nama_barang, merk, warna, stok, harga
  FROM barang
  WHERE id = ?
  LIMIT 1
");
$qOld->bind_param("i", $id);
$qOld->execute();
$old = $qOld->get_result()->fetch_assoc();
$qOld->close();

if (!$old) {
  header("Location: item.php?error=itemnotfound");
  exit;
}

/**
 * =========================
 * DATA BARU
 * =========================
 */
$new = [
  'nama_barang' => trim($_POST['nama_barang']),
  'merk'        => trim($_POST['merk']),
  'warna'       => trim($_POST['warna']),
  'stok'        => (int) $_POST['stok'],
  'harga'       => (float) $_POST['harga'],
];

/**
 * =========================
 * UPDATE DATABASE
 * =========================
 */
$stmt = $conn->prepare("
  UPDATE barang SET
    nama_barang = ?,
    merk        = ?,
    warna       = ?,
    stok        = ?,
    harga       = ?
  WHERE id = ?
");
$stmt->bind_param(
  "sssidi",
  $new['nama_barang'],
  $new['merk'],
  $new['warna'],
  $new['stok'],
  $new['harga'],
  $id
);

if (!$stmt->execute()) {
  header("Location: item.php?error=update_failed");
  exit;
}

/**
 * =========================
 * DETEKSI PERUBAHAN (DIFF)
 * =========================
 */
$changes = [];

foreach ($new as $field => $value) {
  if ($old[$field] != $value) {

    // Formatting khusus
    if ($field === 'harga') {
      $oldVal = 'Rp ' . number_format($old[$field], 0, ',', '.');
      $newVal = 'Rp ' . number_format($value, 0, ',', '.');
    } else {
      $oldVal = $old[$field];
      $newVal = $value;
    }

    $label = ucfirst(str_replace('_', ' ', $field));
    $changes[] = "- $label: $oldVal → $newVal";
  }
}

/**
 * =========================
 * INSERT NOTIFIKASI (JIKA ADA PERUBAHAN)
 * =========================
 */
if (!empty($changes)) {

  $pesan =
    "Super Admin memperbarui data barang (ID : $id, Nama : {$old['nama_barang']})\n" .
    "Perubahan :\n" .
    implode("\n", $changes);

  insertNotifikasiDB(
    $conn,
    $admin_id,
    null,
    $pesan
  );
}

header("Location: item.php?success=item_updated");
exit;
