<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  header("Location: login.php");
  exit;
}

if (!isset($_POST['submit'])) {
  header("Location: form-item-request.php");
  exit;
}

/* ==========================
   AMBIL & VALIDASI INPUT
========================== */
$nama_barang = trim($_POST['nama_barang']);
$merk        = trim($_POST['merk']);
$warna       = trim($_POST['warna']);
$jumlah      = (int) $_POST['jumlah'];

if ($nama_barang === '' || $jumlah <= 0) {
  header("Location: form-item-request.php?error=invalid_input");
  exit;
}

/* ==========================
   AMBIL NAMA CUSTOMER
========================== */
$qCustomer = $conn->prepare("
  SELECT name FROM users WHERE id = ? LIMIT 1
");
$qCustomer->bind_param("i", $user_id);
$qCustomer->execute();
$customer = $qCustomer->get_result()->fetch_assoc();
$nama_customer = $customer['name'] ?? 'Customer';

/* ==========================
   GENERATE KODE PERMINTAAN
========================== */
$qLast = $conn->query("
  SELECT id FROM permintaan_barang
  ORDER BY id DESC
  LIMIT 1
");
$next = ($qLast && $qLast->num_rows > 0)
  ? ((int)$qLast->fetch_assoc()['id'] + 1)
  : 1;

$kode_permintaan = 'PRM-' . str_pad($next, 3, '0', STR_PAD_LEFT);

/* ==========================
   TRANSAKSI DATABASE
========================== */
$conn->begin_transaction();

try {

  /* INSERT PERMINTAAN */
  $stmt = $conn->prepare("
    INSERT INTO permintaan_barang
      (kode_permintaan, user_id, nama_barang, merk, warna, jumlah, status, created_at)
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
  $stmt->execute();

  $permintaan_id = $conn->insert_id;

  /* ==================================================
     NOTIFIKASI 1 — CUSTOMER
  ================================================== */
  $pesan_customer =
    "Permintaan barang Anda berhasil dikirim.\n\n" .
    "Kode Permintaan: $kode_permintaan\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah\n\n" .
    "Silakan menunggu proses verifikasi dari admin.";

  insertNotifikasi(
    $conn,
    $user_id,        // receiver customer
    $user_id,        // sender customer
    $permintaan_id,
    '',              // pesan admin kosong
    $pesan_customer
  );

  /* ==================================================
     NOTIFIKASI 2 — INTERNAL (ADMIN & SUPER ADMIN)
  ================================================== */
  $pesan_admin =
    "Permintaan barang baru diajukan.\n\n" .
    "Kode Permintaan: $kode_permintaan\n" .
    "Customer: $nama_customer\n" .
    "Barang: $nama_barang\n" .
    "Merk: $merk\n" .
    "Warna: $warna\n" .
    "Jumlah: $jumlah\n\n" .
    "Status: MENUNGGU VERIFIKASI";

  // Kirim SATU notifikasi internal (dibaca admin & super admin)
  $qInternal = $conn->query("
    SELECT id FROM users
    WHERE role_id IN (2,3)
      AND deleted_at IS NULL
    LIMIT 1
  ");

  if ($qInternal && $internal = $qInternal->fetch_assoc()) {
    insertNotifikasi(
      $conn,
      $internal['id'], // receiver internal
      $user_id,        // sender customer
      $permintaan_id,
      $pesan_admin,
      null
    );
  }

  $conn->commit();

  header("Location: history-item-request.php?success=item_request_sent");
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  header("Location: form-item-request.php?error=system_error");
  exit;
}
