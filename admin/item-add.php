<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$admin_id = (int) $_SESSION['user_id'];

if (!isset($_POST['tambah_barang'])) {
  header('Location: item.php');
  exit;
}

/* =========================
   DATA INPUT
========================= */
$nama_barang = trim($_POST['nama_barang']);
$merk        = trim($_POST['merk']);
$warna       = trim($_POST['warna']);
$stok        = (int) $_POST['stok'];
$harga       = (float) $_POST['harga'];

if ($nama_barang === '' || $merk === '' || $warna === '' || $stok < 0 || $harga <= 0) {
  header("Location: item.php?error=data_invalid");
  exit;
}

/* =========================
   INSERT BARANG
========================= */
$stmt = $conn->prepare("
  INSERT INTO barang (nama_barang, merk, warna, stok, harga)
  VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param(
  "sssii",
  $nama_barang,
  $merk,
  $warna,
  $stok,
  $harga
);

if (!$stmt->execute()) {
  header("Location: item.php?error=add_failed");
  exit;
}

$barang_id = $conn->insert_id;

/* =========================
   PESAN NOTIFIKASI (ADMIN)
========================= */
$pesan_admin =
  "Barang baru ditambahkan oleh Admin\n\n" .
  "ID Barang   : $barang_id\n" .
  "Nama        : $nama_barang\n" .
  "Merk/Warna  : $merk / $warna\n" .
  "Stok Awal   : $stok\n" .
  "Harga       : Rp " . number_format($harga, 0, ',', '.');

/* =========================
   KIRIM KE SEMUA ADMIN
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
    (int) $admin['id'], // receiver admin
    $admin_id,     // sender admin
    null,               // tidak terkait permintaan
    $pesan_admin,
    null
  );
}

header("Location: item.php?success=item_added");
exit;
