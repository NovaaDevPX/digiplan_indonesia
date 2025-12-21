<?php
// Ambil ID user dari session
$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'];

// ==============================
// TOTAL PERMINTAAN
// ==============================
$q_total = $conn->query("
  SELECT COUNT(*) AS total
  FROM permintaan_barang
  WHERE user_id = $user_id
");
$total_permintaan = $q_total->fetch_assoc()['total'] ?? 0;

// ==============================
// PROSES
// ==============================
$q_proses = $conn->query("
  SELECT COUNT(*) AS total
  FROM permintaan_barang
  WHERE user_id = $user_id
  AND status IN ('diajukan','dalam_pengadaan','siap_distribusi')
");
$proses = $q_proses->fetch_assoc()['total'] ?? 0;

// ==============================
// DITERIMA
// ==============================
$q_diterima = $conn->query("
  SELECT COUNT(*) AS total
  FROM permintaan_barang
  WHERE user_id = $user_id
  AND status IN ('disetujui','selesai')
");
$diterima = $q_diterima->fetch_assoc()['total'] ?? 0;

// ==============================
// DITOLAK
// ==============================
$q_ditolak = $conn->query("
  SELECT COUNT(*) AS total
  FROM permintaan_barang
  WHERE user_id = $user_id
  AND status = 'ditolak'
");
$ditolak = $q_ditolak->fetch_assoc()['total'] ?? 0;

// ==============================
// BARANG (HANYA YANG ADA GAMBAR)
// ==============================
$produk_q = $conn->query("
  SELECT 
    id,
    nama_barang,
    deskripsi,
    harga,
    gambar
  FROM barang
  WHERE gambar IS NOT NULL
    AND gambar != ''
  ORDER BY id DESC
  LIMIT 6
");

// ==============================
// PEMBAYARAN TERAKHIR
// ==============================
$pembayaran = null;

$q_bayar = $conn->query("
  SELECT p.status, p.jumlah, p.tanggal_bayar AS tanggal
  FROM pembayaran p
  JOIN invoice i ON p.id_invoice = i.id_invoice
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang pb ON d.permintaan_id = pb.id
  WHERE pb.user_id = $user_id
  ORDER BY p.tanggal_bayar DESC
  LIMIT 1
");

if ($q_bayar && $q_bayar->num_rows > 0) {
  $pembayaran = $q_bayar->fetch_assoc();
}
