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

// ==========================
// Ambil & validasi input
// ==========================
$nama_barang = trim($_POST['nama_barang']);
$merk        = trim($_POST['merk']);
$warna       = trim($_POST['warna']);
$deskripsi   = trim($_POST['deskripsi'] ?? '');
$jumlah      = intval($_POST['jumlah']);

if (empty($nama_barang) || $jumlah <= 0) {
  echo "<script>alert('Nama barang dan jumlah wajib diisi!');history.back();</script>";
  exit;
}

// ==========================
// Ambil nama customer
// ==========================
$qCustomer = $conn->prepare("
  SELECT name FROM users WHERE id = ? LIMIT 1
");
$qCustomer->bind_param("i", $user_id);
$qCustomer->execute();
$customer = $qCustomer->get_result()->fetch_assoc();

$nama_customer = $customer['name'] ?? 'Customer';

// ==========================
// Generate kode_permintaan
// ==========================
$q = $conn->query("
  SELECT kode_permintaan
  FROM permintaan_barang
  ORDER BY id DESC
  LIMIT 1
");

if ($q->num_rows > 0) {
  $last = $q->fetch_assoc()['kode_permintaan'];
  $num  = (int) substr($last, 4);
  $next = $num + 1;
} else {
  $next = 1;
}

$kode_permintaan = 'PRM-' . str_pad($next, 3, '0', STR_PAD_LEFT);

// ==========================
// Simpan ke database
// ==========================
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

  // ==========================
  // NOTIFIKASI DATABASE ONLY
  // ==========================

  // Ambil semua Super Admin
  $qSuperadmin = $conn->query("
    SELECT id FROM users
    WHERE role_id = 3 AND deleted_at IS NULL
  ");

  while ($sa = $qSuperadmin->fetch_assoc()) {
    insertNotifikasiDB(
      $conn,
      $sa['id'],
      $permintaan_id,
      "Customer $nama_customer mengajukan permintaan barang dengan kode $kode_permintaan."
    );
  }

  echo "<script>
    alert('Permintaan berhasil dikirim dengan kode $kode_permintaan');
    window.location='history-item-request.php';
  </script>";
} else {
  echo "<script>alert('Gagal menyimpan data');history.back();</script>";
}
