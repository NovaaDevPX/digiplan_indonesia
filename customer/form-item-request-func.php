<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  header("Location: login.php");
  exit;
}

if (!isset($_POST['submit'])) {
  header("Location: form-item-request.php");
  exit;
}

/**
 * ==========================
 * AMBIL & VALIDASI INPUT
 * ==========================
 */
$nama_barang = trim($_POST['nama_barang']);
$merk        = trim($_POST['merk']);
$warna       = trim($_POST['warna']);
$deskripsi   = trim($_POST['deskripsi'] ?? '');
$jumlah      = (int) $_POST['jumlah'];

if (empty($nama_barang) || $jumlah <= 0) {
  header("Location: form-item-request.php?error=invalid_input");
  exit;
}

/**
 * ==========================
 * AMBIL NAMA CUSTOMER
 * ==========================
 */
$qCustomer = $conn->prepare("
  SELECT name 
  FROM users 
  WHERE id = ? 
  LIMIT 1
");
$qCustomer->bind_param("i", $user_id);
$qCustomer->execute();
$customer = $qCustomer->get_result()->fetch_assoc();
$nama_customer = $customer['name'] ?? 'Customer';

/**
 * ==========================
 * GENERATE KODE PERMINTAAN
 * ==========================
 */
$q = $conn->query("
  SELECT kode_permintaan
  FROM permintaan_barang
  ORDER BY id DESC
  LIMIT 1
");

if ($q && $q->num_rows > 0) {
  $last = $q->fetch_assoc()['kode_permintaan'];
  $num  = (int) substr($last, 4);
  $next = $num + 1;
} else {
  $next = 1;
}

$kode_permintaan = 'PRM-' . str_pad($next, 3, '0', STR_PAD_LEFT);

/**
 * ==========================
 * SIMPAN PERMINTAAN
 * ==========================
 */
$stmt = $conn->prepare("
  INSERT INTO permintaan_barang
  (
    kode_permintaan,
    user_id,
    nama_barang,
    merk,
    warna,
    jumlah,
    status,
    created_at
  )
  VALUES (?, ?, ?, ?, ?, ?, 'diajukan', NOW())
");

if (!$stmt) {
  header("Location: form-item-request.php?error=db_prepare");
  exit;
}

$stmt->bind_param(
  "sisssi",
  $kode_permintaan,
  $user_id,
  $nama_barang,
  $merk,
  $warna,
  $jumlah
);

if ($stmt->execute()) {

  $permintaan_id = $conn->insert_id;

  /**
   * ==========================
   * NOTIFIKASI DATABASE (DISESUAIKAN)
   * ==========================
   */

  /* Notifikasi untuk ADMIN / SISTEM */
  $pesan_admin =
    "Permintaan barang baru diajukan.\n\n" .
    "Kode Permintaan: $kode_permintaan\n" .
    "Customer: $nama_customer\n" .
    "Barang: $nama_barang\n" .
    "Merk: $merk\n" .
    "Warna: $warna\n" .
    "Jumlah: $jumlah\n\n" .
    "Silakan lakukan pengecekan stok dan proses selanjutnya.";

  /* Notifikasi untuk CUSTOMER */
  $pesan_customer =
    "Permintaan barang Anda berhasil dikirim.\n\n" .
    "Kode Permintaan: $kode_permintaan\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah\n\n" .
    "Permintaan akan segera diproses oleh admin.";

  insertNotifikasi(
    $conn,
    $user_id,        // receiver CUSTOMER
    $user_id,        // sender CUSTOMER (self)
    $permintaan_id,
    $pesan_admin,
    $pesan_customer
  );

  header("Location: history-item-request.php?success=item_request_sent");
  exit;
} else {
  header("Location: form-item-request.php?error=save_failed");
  exit;
}
