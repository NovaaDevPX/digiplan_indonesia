<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin'], true);

header('Content-Type: application/json');

/* ================================
   AMBIL PARAMETER
================================ */
$nama_barang = trim($_GET['nama_barang'] ?? '');
$merk        = trim($_GET['merk'] ?? '');
$warna       = trim($_GET['warna'] ?? '');
$jumlah_req  = (int)($_GET['jumlah'] ?? 0);

if ($nama_barang === '' || $merk === '' || $warna === '' || $jumlah_req <= 0) {
  echo json_encode([
    'found'   => false,
    'status'  => 'error',
    'message' => 'Parameter tidak lengkap.'
  ]);
  exit;
}

/* ================================
   CEK BARANG DI TABEL BARANG
================================ */
$stmt = $conn->prepare("
  SELECT id, harga, stok
  FROM barang
  WHERE nama_barang = ?
    AND merk = ?
    AND warna = ?
    AND deleted_at IS NULL
  LIMIT 1
");

$stmt->bind_param("sss", $nama_barang, $merk, $warna);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();

if (!$barang) {
  echo json_encode([
    'found'   => false,
    'status'  => 'not_found',
    'message' => 'Barang tidak ditemukan di gudang.'
  ]);
  exit;
}

$barang_id = (int)$barang['id'];
$stok      = (int)$barang['stok'];
$harga     = (float)$barang['harga'];

/* ================================
   AMBIL SUPPLIER TERAKHIR
================================ */
$getSup = $conn->prepare("
  SELECT supplier, kontak_supplier, alamat_supplier
  FROM pengadaan_barang
  WHERE barang_id = ?
    AND deleted_at IS NULL
  ORDER BY created_at DESC
  LIMIT 1
");

$getSup->bind_param("i", $barang_id);
$getSup->execute();
$rSup = $getSup->get_result()->fetch_assoc();

$last_supplier        = $rSup['supplier'] ?? null;
$last_kontak_supplier = $rSup['kontak_supplier'] ?? null;
$last_alamat_supplier = $rSup['alamat_supplier'] ?? null;

/* ================================
   HITUNG STATUS STOK
================================ */
if ($stok >= $jumlah_req) {
  $status  = 'cukup';
  $jumlah_pengadaan = 0;
  $message = "Stok tersedia ($stok unit). Tidak perlu pengadaan.";
} else {
  $status = 'kurang';
  $jumlah_pengadaan = $jumlah_req - $stok;
  $message = "Stok hanya $stok unit. Perlu pengadaan $jumlah_pengadaan unit.";
}

/* ================================
   RESPONSE JSON
================================ */
echo json_encode([
  'found'             => true,
  'status'            => $status,
  'barang_id'         => $barang_id,
  'harga'             => $harga,
  'stok'              => $stok,
  'jumlah_permintaan' => $jumlah_req,
  'jumlah_pengadaan'  => $jumlah_pengadaan,
  'message'           => $message,

  // Supplier otomatis
  'supplier'          => $last_supplier,
  'kontak_supplier'   => $last_kontak_supplier,
  'alamat_supplier'   => $last_alamat_supplier
]);
exit;
