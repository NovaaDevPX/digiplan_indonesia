<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

$superadmin_id = (int) $_SESSION['user_id'];

if (!isset($_POST['edit_barang'])) {
  header('Location: item.php');
  exit;
}

$id = (int) $_POST['id'];

/* =========================
   DATA SEBELUM UPDATE
========================= */
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

/* =========================
   DATA BARU
========================= */
$new = [
  'nama_barang' => trim($_POST['nama_barang']),
  'merk'        => trim($_POST['merk']),
  'warna'       => trim($_POST['warna']),
  'stok'        => (int) $_POST['stok'],
  'harga'       => (float) $_POST['harga'],
];

/* =========================
   UPDATE DATABASE
========================= */
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

/* =========================
   DETEKSI PERUBAHAN
========================= */
$changes = [];

foreach ($new as $field => $value) {
  if ($old[$field] != $value) {

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

/* =========================
   NOTIFIKASI (ADMIN)
========================= */
if (!empty($changes)) {

  $pesan_admin =
    "Data barang diperbarui oleh Super Admin\n\n" .
    "ID Barang : $id\n" .
    "Nama      : {$old['nama_barang']}\n\n" .
    "Perubahan:\n" .
    implode("\n", $changes);

  /* =========================
     AMBIL SEMUA ADMIN
  ========================= */
  $qAdmin = $conn->query("
    SELECT id
    FROM users
    WHERE role_id = 2
      AND deleted_at IS NULL
  ");

  while ($admin = $qAdmin->fetch_assoc()) {
    insertNotifikasi(
      $conn,
      (int) $admin['id'], // RECEIVER ADMIN
      $superadmin_id,     // SENDER SUPER ADMIN
      null,               // tidak terkait permintaan
      $pesan_admin,       // pesan admin
      null                // tidak ada pesan customer
    );
  }
}

header("Location: item.php?success=item_updated");
exit;
