<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

if (!isset($_POST['data'])) {
  die("ERROR: Data tidak ditemukan");
}

list($pengadaan_id, $permintaan_id) = explode('|', $_POST['data']);

$kode   = $_POST['kode_distribusi'];
$alamat = $_POST['alamat'];
$kurir  = strtoupper(str_replace(' ', '', $_POST['kurir']));
$admin  = $_SESSION['user_id'];

$tanggal = date('Ymd');
$random  = rand(1000, 9999);
$no_resi = "RESI-$kurir-$tanggal-$random";

/* ============================================================
   AMBIL DATA BARANG DARI PERMINTAAN
   COCOKKAN DENGAN BARANG (nama + merk + warna)
============================================================ */
$qDetail = mysqli_query($conn, "
  SELECT 
    b.id AS barang_id,
    b.nama_barang AS nama_barang,
    pm.jumlah,
    pm.nama_barang AS pm_nama,
    pm.merk AS pm_merk,
    pm.warna AS pm_warna
  FROM permintaan_barang pm
  JOIN barang b 
       ON b.nama_barang = pm.nama_barang
      AND b.merk = pm.merk
      AND b.warna = pm.warna
  WHERE pm.id = '$permintaan_id'
");

if (!$qDetail) {
  die("ERROR QUERY DETAIL: " . mysqli_error($conn));
}

$detail = mysqli_fetch_assoc($qDetail);

if (!$detail) {
  die("ERROR: Barang tidak cocok dengan data permintaan. 
       Pastikan nama_barang, merk, dan warna sama persis.");
}

$barang_id      = $detail['barang_id'];
$nama_barang    = $detail['nama_barang'];
$jumlah_request = (int)$detail['jumlah'];

/* ============================================================
   MULAI TRANSAKSI
============================================================ */
mysqli_begin_transaction($conn);

try {

  /* INSERT DISTRIBUSI */
  $insertDistribusi = mysqli_query($conn, "
    INSERT INTO distribusi_barang 
    (kode_distribusi, pengadaan_id, permintaan_id, admin_id, alamat_pengiriman, kurir, no_resi, tanggal_kirim, status_distribusi)
    VALUES
    ('$kode', '$pengadaan_id', '$permintaan_id', '$admin', '$alamat', '$kurir', '$no_resi', CURDATE(), 'dikirim')
  ");

  if (!$insertDistribusi) {
    throw new Exception("Gagal insert distribusi: " . mysqli_error($conn));
  }

  /* UPDATE STATUS PERMINTAAN → selesai */
  $updatePM = mysqli_query($conn, "
    UPDATE permintaan_barang 
    SET status='selesai'
    WHERE id='$permintaan_id'
  ");

  if (!$updatePM) {
    throw new Exception("Gagal update status permintaan: " . mysqli_error($conn));
  }

  /* ============================================================
     UPDATE STOK BARANG
  ============================================================= */

  // Ambil stok lama
  $qStok = mysqli_query($conn, "SELECT stok FROM barang WHERE id = '$barang_id'");
  if (!$qStok) {
    throw new Exception("Query ambil stok gagal: " . mysqli_error($conn));
  }

  $stokData = mysqli_fetch_assoc($qStok);
  if (!$stokData) {
    throw new Exception("Stok barang tidak ditemukan.");
  }

  $stok_lama = (int)$stokData['stok'];

  // Hitung stok baru
  $stok_baru = $stok_lama - $jumlah_request;

  if ($stok_baru < 0) {
    throw new Exception("Stok barang tidak mencukupi untuk permintaan ini.");
  }

  // Update stok barang
  $updateStok = mysqli_query($conn, "
    UPDATE barang 
    SET stok = '$stok_baru'
    WHERE id = '$barang_id'
  ");

  if (!$updateStok) {
    throw new Exception("Gagal update stok barang: " . mysqli_error($conn));
  }

  /* ============================================================
     AMBIL KODE PERMINTAAN
  ============================================================= */
  $qPm = mysqli_query($conn, "
    SELECT kode_permintaan 
    FROM permintaan_barang 
    WHERE id = '$permintaan_id'
  ");

  if (!$qPm) {
    throw new Exception("Query ambil kode permintaan gagal: " . mysqli_error($conn));
  }

  $pm = mysqli_fetch_assoc($qPm);

  /* ============================================================
     NOTIFIKASI
  ============================================================= */
  $pesan =
    "Admin membuat distribusi baru\n" .
    "Kode Distribusi: $kode\n" .
    "Kode Permintaan: {$pm['kode_permintaan']}\n" .
    "Barang: $nama_barang\n" .
    "Jumlah Dikirim: $jumlah_request\n" .
    "Kurir: $kurir\n" .
    "No Resi: $no_resi\n" .
    "Alamat Kirim: $alamat\n" .
    "Status: Dikirim";

  insertNotifikasiDB($conn, $admin, $permintaan_id, $pesan);

  /* COMMIT DATABASE */
  mysqli_commit($conn);

  header("Location: distribution.php?success=item_distribution_success&kode=$kode&pdf=true");
  exit;
} catch (Exception $e) {
  mysqli_rollback($conn);
  die("ERROR: " . $e->getMessage());
}
