<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

function queryCheck($conn, $query)
{
  $result = mysqli_query($conn, $query);
  if (!$result) {
    die("Query error: " . mysqli_error($conn));
  }
  return $result;
}

$today = date('Y-m-d');
$currentMonth = date('Y-m');

/**
 * PERMINTAAN HARI INI
 * pakai created_at (karena tanggal_permintaan TIDAK ADA)
 */
$q1 = queryCheck(
  $conn,
  "SELECT COUNT(*) AS total 
   FROM permintaan_barang 
   WHERE DATE(created_at) = '$today'"
);
$permintaan_hari_ini = mysqli_fetch_assoc($q1)['total'] ?? 0;

/**
 * PERMINTAAN DISETUJUI
 * enum di DB: disetujui (huruf kecil)
 */
$q2 = queryCheck(
  $conn,
  "SELECT COUNT(*) AS total 
   FROM permintaan_barang 
   WHERE status = 'disetujui'"
);
$permintaan_diterima = mysqli_fetch_assoc($q2)['total'] ?? 0;

/**
 * BARANG MASUK BULAN INI
 * kolom tanggal yang benar: tanggal_pengadaan
 */
$q3 = queryCheck(
  $conn,
  "SELECT IFNULL(SUM(jumlah),0) AS total 
   FROM pengadaan_barang 
   WHERE DATE_FORMAT(tanggal_pengadaan, '%Y-%m') = '$currentMonth'
     AND status_pengadaan = 'selesai'"
);
$barang_masuk = mysqli_fetch_assoc($q3)['total'] ?? 0;

/**
 * BARANG KELUAR BULAN INI
 * kolom tanggal yang benar: tanggal_kirim
 */
$q4 = queryCheck(
  $conn,
  "SELECT IFNULL(SUM(p.jumlah),0) AS total 
   FROM distribusi_barang d
   JOIN pengadaan_barang p ON d.pengadaan_id = p.id
   WHERE DATE_FORMAT(d.tanggal_kirim, '%Y-%m') = '$currentMonth'
     AND d.status_distribusi IN ('dikirim','diterima')"
);
$barang_keluar = mysqli_fetch_assoc($q4)['total'] ?? 0;
