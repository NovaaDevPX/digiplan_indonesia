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
 * =========================
 * STATISTIK DASHBOARD
 * =========================
 */

$q1 = queryCheck(
  $conn,
  "SELECT COUNT(*) AS total 
   FROM permintaan_barang 
   WHERE DATE(created_at) = '$today'"
);
$permintaan_hari_ini = mysqli_fetch_assoc($q1)['total'] ?? 0;

$q2 = queryCheck(
  $conn,
  "SELECT COUNT(*) AS total 
   FROM permintaan_barang 
   WHERE status = 'disetujui'"
);
$permintaan_diterima = mysqli_fetch_assoc($q2)['total'] ?? 0;

$q3 = queryCheck(
  $conn,
  "SELECT IFNULL(SUM(jumlah),0) AS total 
   FROM pengadaan_barang 
   WHERE DATE_FORMAT(tanggal_pengadaan, '%Y-%m') = '$currentMonth'
     AND status_pengadaan = 'selesai'"
);
$barang_masuk = mysqli_fetch_assoc($q3)['total'] ?? 0;

$q4 = queryCheck(
  $conn,
  "SELECT IFNULL(SUM(p.jumlah),0) AS total 
   FROM distribusi_barang d
   JOIN pengadaan_barang p ON d.pengadaan_id = p.id
   WHERE DATE_FORMAT(d.tanggal_kirim, '%Y-%m') = '$currentMonth'
     AND d.status_distribusi IN ('dikirim','diterima')"
);
$barang_keluar = mysqli_fetch_assoc($q4)['total'] ?? 0;

/**
 * =========================
 * NOTIFIKASI ADMIN
 * =========================
 * user_id = PEMBUAT NOTIFIKASI
 * Admin melihat notifikasi dari Super Admin
 */

// Tandai dibaca (opsional, semua notifikasi super admin)
$conn->query("
  UPDATE notifikasi n
  JOIN users u ON n.user_id = u.id
  SET n.status_baca = 1
  WHERE u.role_id = 3
");

// Ambil notifikasi terbaru dari Super Admin
$qNotif = queryCheck(
  $conn,
  "
  SELECT 
    n.id,
    n.pesan,
    n.status_baca,
    n.created_at,
    p.kode_permintaan,
    u.name AS pembuat,
    u.role_id
  FROM notifikasi n
  JOIN users u ON n.user_id = u.id
  LEFT JOIN permintaan_barang p ON n.permintaan_id = p.id
  WHERE u.role_id IN (1, 2, 3)
  ORDER BY n.created_at DESC
  LIMIT 10
  "
);


$notifikasi = [];
while ($row = mysqli_fetch_assoc($qNotif)) {
  $notifikasi[] = $row;
}
