<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin'], true);

header('Content-Type: application/json');

/* ==========================
   VALIDASI PARAMETER
========================== */
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

/* ==========================
   CARI BARANG
========================== */
$stmt = $conn->prepare("
  SELECT id, stok, harga
  FROM barang
  WHERE nama_barang = ?
    AND merk = ?
    AND warna = ?
    AND deleted_at IS NULL
  LIMIT 1
");
$stmt->bind_param("sss", $nama_barang, $merk, $warna);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();

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

/* ==========================
   CARI SUPPLIER TERAKHIR VALID
   (LEWATI STOK_GUDANG AUTO)
========================== */
$q = $conn->prepare("
  SELECT p1.supplier, p1.kontak_supplier, p1.alamat_supplier
  FROM pengadaan_barang p1
  WHERE p1.barang_id = ?
    AND p1.deleted_at IS NULL
    AND p1.supplier IS NOT NULL
    AND p1.supplier <> ''
    AND p1.supplier <> 'STOK_GUDANG ( AUTO )'
    AND NOT EXISTS (
      SELECT 1
      FROM pengadaan_barang p2
      WHERE p2.barang_id = p1.barang_id
        AND p2.deleted_at IS NULL
        AND p2.id > p1.id
        AND p2.supplier <> 'STOK_GUDANG ( AUTO )'
    )
  LIMIT 1
");
$q->bind_param("i", $barang_id);
$q->execute();
$r = $q->get_result()->fetch_assoc();

/* ==========================
   DATA SUPPLIER (BISA NULL)
========================== */
$last_supplier        = $r['supplier'] ?? null;
$last_kontak_supplier = $r['kontak_supplier'] ?? null;
$last_alamat_supplier = $r['alamat_supplier'] ?? null;

/* ==========================
   HITUNG STOK
========================== */
if ($stok >= $jumlah_req) {
  $status = 'cukup';
  $jumlah_pengadaan = 0;
  $message = "Stok cukup ($stok unit). Tidak perlu pengadaan.";
} else {
  $status = 'kurang';
  $jumlah_pengadaan = $jumlah_req - $stok;
  $message = "Stok hanya $stok unit. Perlu pengadaan $jumlah_pengadaan unit.";
}

/* ==========================
   RESPONSE JSON
========================== */
echo json_encode([
  'found'            => true,
  'status'           => $status,
  'barang_id'        => $barang_id,
  'harga'            => $harga,
  'stok'             => $stok,
  'jumlah_pengadaan' => $jumlah_pengadaan,
  'message'          => $message,

  'supplier'         => $last_supplier,
  'kontak_supplier'  => $last_kontak_supplier,
  'alamat_supplier'  => $last_alamat_supplier
]);
exit;
