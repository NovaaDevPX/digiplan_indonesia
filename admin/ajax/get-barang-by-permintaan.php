<?php
require '../../include/conn.php';
require '../../include/auth.php';

cek_role(['admin'], true);

header('Content-Type: application/json');

$nama  = trim($_GET['nama_barang'] ?? '');
$merk  = trim($_GET['merk'] ?? '');
$warna = trim($_GET['warna'] ?? '');

if ($nama === '' || $merk === '' || $warna === '') {
  echo json_encode(['found' => false]);
  exit;
}

$stmt = $conn->prepare("
  SELECT id, harga
  FROM barang
  WHERE nama_barang = ?
    AND merk = ?
    AND warna = ?
    AND deleted_at IS NULL
  LIMIT 1
");
$stmt->bind_param("sss", $nama, $merk, $warna);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
  echo json_encode([
    'found'     => true,
    'barang_id' => $result['id'],
    'harga'     => $result['harga']
  ]);
} else {
  echo json_encode(['found' => false]);
}
