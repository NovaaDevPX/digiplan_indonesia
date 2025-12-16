<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

list($pengadaan_id, $permintaan_id) = explode('|', $_POST['data']);

$kode   = $_POST['kode_distribusi'];
$alamat = $_POST['alamat'];
$kurir  = strtoupper(str_replace(' ', '', $_POST['kurir']));
$admin  = $_SESSION['user_id'];

/* Generate no resi */
$tanggal = date('Ymd');
$random  = rand(1000, 9999);
$no_resi = "RESI-$kurir-$tanggal-$random";

/* Simpan distribusi */
mysqli_query($conn, "
  INSERT INTO distribusi_barang 
  (kode_distribusi, pengadaan_id, permintaan_id, admin_id, alamat_pengiriman, kurir, no_resi, tanggal_kirim, status_distribusi)
  VALUES
  ('$kode', '$pengadaan_id', '$permintaan_id', '$admin', '$alamat', '$kurir', '$no_resi', CURDATE(), 'dikirim')
");

/* Update status permintaan */
mysqli_query($conn, "
  UPDATE permintaan_barang 
  SET status='selesai'
  WHERE id='$permintaan_id'
");

/* Ambil kode permintaan */
$q = mysqli_query($conn, "
  SELECT kode_permintaan 
  FROM permintaan_barang 
  WHERE id = '$permintaan_id'
");
$pm = mysqli_fetch_assoc($q);

/* NOTIFIKASI */
$pesan =
  "Admin membuat distribusi baru\n" .
  "Kode Distribusi: $kode\n" .
  "Kode Permintaan: {$pm['kode_permintaan']}\n" .
  "Kurir: $kurir\n" .
  "No Resi: $no_resi\n" .
  "Alamat Kirim: $alamat\n" .
  "Status: Dikirim";

insertNotifikasiDB($conn, $admin, $permintaan_id, $pesan);

header("Location: distribution.php?success=item_distribution_success&kode=$kode&pdf=true");
exit;
