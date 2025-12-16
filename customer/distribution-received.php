<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'];
$id = (int) $_GET['id'];

/* =========================
   VALIDASI + AMBIL DATA
========================= */
$q = $conn->query("
  SELECT 
    d.id,
    d.kode_distribusi,
    d.permintaan_id,
    p.kode_permintaan,
    u.nama AS nama_customer
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE d.id = $id
    AND p.user_id = $user_id
    AND d.status_distribusi = 'dikirim'
  LIMIT 1
");

if (!$q || $q->num_rows === 0) {
  die('Akses tidak valid');
}

$data = $q->fetch_assoc();

/* =========================
   UPDATE STATUS DISTRIBUSI
========================= */
$update = $conn->query("
  UPDATE distribusi_barang
  SET status_distribusi = 'diterima',
      tanggal_terima = NOW()
  WHERE id = $id
");

if (!$update) {
  die('Gagal update distribusi: ' . $conn->error);
}

/* =========================
   NOTIFIKASI
========================= */
$pesan =
  "Customer ({$data['nama_customer']}) telah mengonfirmasi penerimaan barang.\n" .
  "Kode Distribusi: {$data['kode_distribusi']}\n" .
  "Kode Permintaan: {$data['kode_permintaan']}\n" .
  "Status: Dikirim → Diterima";

$notif = insertNotifikasiDB(
  $conn,
  $user_id,
  $data['permintaan_id'],
  $pesan
);

if (!$notif) {
  die('Gagal insert notifikasi: ' . $conn->error);
}

/* =========================
   REDIRECT
========================= */
header("Location: history-item-request.php?success=diterima");
exit;
