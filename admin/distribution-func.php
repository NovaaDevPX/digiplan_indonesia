<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

list($pengadaan_id, $permintaan_id) = explode('|', $_POST['data']);

$kode   = $_POST['kode_distribusi'];
$alamat = $_POST['alamat'];
$kurir  = strtoupper(str_replace(' ', '', $_POST['kurir']));
$admin  = $_SESSION['user_id'];

/* =========================
   GENERATE NO RESI OTOMATIS
   ========================= */
$tanggal = date('Ymd');
$random  = rand(1000, 9999);
$no_resi = "RESI-$kurir-$tanggal-$random";

/* simpan distribusi */
mysqli_query($conn, "
INSERT INTO distribusi_barang 
(kode_distribusi, pengadaan_id, permintaan_id, admin_id, alamat_pengiriman, kurir, no_resi, tanggal_kirim, status_distribusi)
VALUES
('$kode', '$pengadaan_id', '$permintaan_id', '$admin', '$alamat', '$kurir', '$no_resi', CURDATE(), 'dikirim')
");

/* update status permintaan */
mysqli_query($conn, "
UPDATE permintaan_barang 
SET status='selesai'
WHERE id='$permintaan_id'
");

header("Location: distribution.php?success=item_distribution_success&kode=$kode&pdf=true");
exit;
