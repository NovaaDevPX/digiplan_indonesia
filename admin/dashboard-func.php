<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

function queryCheck($conn, $query)
{
  $result = mysqli_query($conn, $query);
  if (!$result) die("Query error: " . mysqli_error($conn));
  return $result;
}

$today = date('Y-m-d');
$currentMonth = date('Y-m');

$q1 = queryCheck($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE DATE(tanggal_permintaan) = '$today'");
$permintaan_hari_ini = mysqli_fetch_assoc($q1)['total'] ?? 0;

$q2 = queryCheck($conn, "SELECT COUNT(*) AS total FROM permintaan_barang WHERE status = 'Diterima'");
$permintaan_diterima = mysqli_fetch_assoc($q2)['total'] ?? 0;

$q3 = queryCheck($conn, "SELECT SUM(jumlah) AS total FROM pengadaan_barang WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$currentMonth'");
$barang_masuk = mysqli_fetch_assoc($q3)['total'] ?? 0;

$q4 = queryCheck($conn, "SELECT SUM(jumlah) AS total FROM distribusi_barang WHERE DATE_FORMAT(tanggal_pengiriman, '%Y-%m') = '$currentMonth'");
$barang_keluar = mysqli_fetch_assoc($q4)['total'] ?? 0;
