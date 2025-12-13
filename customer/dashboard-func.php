<?php
// Ambil ID user dari session
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Ambil data permintaan user dari tabel permintaan_barang
// Status bisa "proses", "diterima", "ditolak"
$q_proses = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'proses'");
$q_diterima = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'diterima'");
$q_ditolak = mysqli_query($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE user_id = '$user_id' AND LOWER(status) = 'ditolak'");

$produk_q = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC LIMIT 6");

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
// diajukan + dalam_pengadaan + siap_distribusi
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
// disetujui + selesai
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
// PRODUK
// ==============================
$produk_q = $conn->query("
  SELECT * FROM produk
  ORDER BY created_at DESC
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


// Total jumlah permintaan
$q_total = mysqli_query(
  $conn,
  "SELECT COUNT(*) AS total 
     FROM permintaan_barang 
     WHERE user_id = '$user_id'"
);

$total_permintaan = ($q_total) ? mysqli_fetch_assoc($q_total)['total'] : 0;

// Informasi pembayaran terakhir user
$q_bayar = mysqli_query(
  $conn,
  "SELECT * FROM pembayaran 
     WHERE user_id = '$user_id' 
     ORDER BY tanggal DESC 
     LIMIT 1"
);

$pembayaran = ($q_bayar) ? mysqli_fetch_assoc($q_bayar) : null;
