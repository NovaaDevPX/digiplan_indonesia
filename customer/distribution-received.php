<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['customer']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($user_id <= 0 || $id <= 0) {
  die('Akses tidak valid (parameter)');
}

/* =========================
   AMBIL DATA DISTRIBUSI
========================= */
$sql = "
  SELECT 
    d.id,
    d.kode_distribusi,
    LOWER(TRIM(d.status_distribusi)) AS status_distribusi,
    d.permintaan_id,
    p.kode_permintaan,
    LOWER(TRIM(p.status)) AS status_permintaan,
    u.name AS nama_customer
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE d.id = ?
    AND p.user_id = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die('Akses tidak valid (data tidak ditemukan)');
}

$data = $result->fetch_assoc();

/* =========================
   VALIDASI STATUS
========================= */
if ($data['status_permintaan'] !== 'selesai') {
  die('Permintaan belum selesai');
}

if ($data['status_distribusi'] !== 'dikirim') {
  die('Distribusi belum dikirim atau sudah diterima');
}

/* =========================
   UPDATE STATUS DISTRIBUSI
========================= */
$update = $conn->prepare("
  UPDATE distribusi_barang
  SET status_distribusi = 'diterima',
      tanggal_terima = NOW()
  WHERE id = ?
");
$update->bind_param("i", $id);
$update->execute();

/* ==================================================
   NOTIFIKASI 1 — CUSTOMER (ARSIP PRIBADI)
================================================== */
$pesan_customer =
  "Anda telah mengonfirmasi penerimaan barang.\n\n" .
  "Kode Distribusi: {$data['kode_distribusi']}\n" .
  "Kode Permintaan: {$data['kode_permintaan']}\n\n" .
  "Terima kasih telah menggunakan layanan kami.";

insertNotifikasi(
  $conn,
  $user_id,                 // receiver customer
  $user_id,                 // sender (self)
  $data['permintaan_id'],
  '',                        // pesan admin dikosongkan
  $pesan_customer
);

/* ==================================================
   NOTIFIKASI 2 — INTERNAL (ADMIN / SUPER ADMIN)
================================================== */
$pesan_admin =
  "Konfirmasi penerimaan barang oleh customer.\n\n" .
  "Customer: {$data['nama_customer']}\n" .
  "Kode Distribusi: {$data['kode_distribusi']}\n" .
  "Kode Permintaan: {$data['kode_permintaan']}\n\n" .
  "Status Distribusi: DITERIMA\n" .
  "Proses permintaan telah selesai sepenuhnya. Tolong super adim membuat Invoice secepatnya !";

$qInternal = $conn->query("
  SELECT id FROM users
  WHERE role_id IN (2,3)
    AND deleted_at IS NULL
  LIMIT 1
");

if ($qInternal && $internal = $qInternal->fetch_assoc()) {
  insertNotifikasi(
    $conn,
    $internal['id'],         // receiver admin / super admin
    $user_id,                // sender customer
    $data['permintaan_id'],
    $pesan_admin,
    ''
  );
}

/* =========================
   REDIRECT
========================= */
header("Location: history-item-request.php?success=diterima");
exit;
