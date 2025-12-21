<?php
require '../../include/conn.php';
require '../../include/auth.php';

cek_role(['super_admin'], true);

header('Content-Type: application/json');

/* =========================
   INPUT
========================= */
$nama   = trim($_GET['nama_barang'] ?? '');
$merk   = trim($_GET['merk'] ?? '');
$warna  = trim($_GET['warna'] ?? '');
$jumlah_permintaan = (int) ($_GET['jumlah'] ?? 0);

/* =========================
   VALIDASI INPUT
========================= */
if (
  $nama === '' ||
  $merk === '' ||
  $warna === '' ||
  $jumlah_permintaan <= 0
) {
  echo json_encode([
    'found' => false,
    'message' => 'Parameter tidak lengkap'
  ]);
  exit;
}

/* =========================
   CEK BARANG
========================= */
$stmt = $conn->prepare("
  SELECT 
    id,
    harga,
    stok
  FROM barang
  WHERE nama_barang = ?
    AND merk = ?
    AND warna = ?
    AND deleted_at IS NULL
  LIMIT 1
");

$stmt->bind_param("sss", $nama, $merk, $warna);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();

/* =========================
   JIKA BARANG TIDAK ADA
========================= */
if (!$barang) {
  echo json_encode([
    'found' => false,
    'message' => 'Barang tidak ditemukan'
  ]);
  exit;
}

/* =========================
   LOGIKA STOK vs PERMINTAAN
========================= */
$stok = (int) $barang['stok'];

if ($stok >= $jumlah_permintaan) {
  // stok cukup → tidak perlu pengadaan
  $jumlah_pengadaan = 0;
} else {
  // stok kurang → beli selisih
  $jumlah_pengadaan = $jumlah_permintaan - $stok;
}

/* =========================
   RESPONSE
========================= */
echo json_encode([
  'found'            => true,
  'barang_id'        => $barang['id'],
  'harga'            => (int) $barang['harga'],
  'stok'             => $stok,
  'jumlah_pengadaan' => $jumlah_pengadaan
]);
