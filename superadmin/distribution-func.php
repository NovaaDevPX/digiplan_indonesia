<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_POST['data'], $_POST['kode_distribusi'], $_POST['alamat'], $_POST['kurir'])) {
  header("Location: distribution.php?error=invalid_request");
  exit;
}

list($pengadaan_id, $permintaan_id) = array_map('intval', explode('|', $_POST['data']));

$kode   = trim($_POST['kode_distribusi']);
$alamat = trim($_POST['alamat']);
$kurir  = strtoupper(str_replace(' ', '', $_POST['kurir']));
$superadmin_id = (int) $_SESSION['user_id'];

/* =========================
   GENERATE RESI
========================= */
$no_resi = sprintf(
  "RESI-%s-%s-%04d",
  $kurir,
  date('Ymd'),
  random_int(1000, 9999)
);

/* =========================
   AMBIL DETAIL PERMINTAAN + BARANG
========================= */
$stmtDetail = $conn->prepare("
  SELECT 
    b.id AS barang_id,
    b.nama_barang,
    pm.jumlah,
    pm.kode_permintaan,
    pm.user_id AS customer_id,
    u.name AS customer_name
  FROM permintaan_barang pm
  JOIN barang b
    ON b.nama_barang = pm.nama_barang
   AND b.merk = pm.merk
   AND b.warna = pm.warna
  JOIN users u ON pm.user_id = u.id
  WHERE pm.id = ?
");
$stmtDetail->bind_param("i", $permintaan_id);
$stmtDetail->execute();
$detail = $stmtDetail->get_result()->fetch_assoc();
$stmtDetail->close();

if (!$detail) {
  header("Location: distribution.php?error=barang_not_match");
  exit;
}

$barang_id      = (int) $detail['barang_id'];
$nama_barang    = $detail['nama_barang'];
$jumlah_request = (int) $detail['jumlah'];

/* =========================
   TRANSAKSI DATABASE
========================= */
$conn->begin_transaction();

try {

  /* INSERT DISTRIBUSI */
  $stmtInsert = $conn->prepare("
    INSERT INTO distribusi_barang
    (kode_distribusi, pengadaan_id, permintaan_id, admin_id, alamat_pengiriman, kurir, no_resi, tanggal_kirim, status_distribusi)
    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'dikirim')
  ");
  $stmtInsert->bind_param(
    "siiisss",
    $kode,
    $pengadaan_id,
    $permintaan_id,
    $superadmin_id,
    $alamat,
    $kurir,
    $no_resi
  );
  $stmtInsert->execute();
  $stmtInsert->close();

  /* UPDATE STATUS PERMINTAAN */
  $stmtPM = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'selesai'
    WHERE id = ?
  ");
  $stmtPM->bind_param("i", $permintaan_id);
  $stmtPM->execute();
  $stmtPM->close();

  /* AMBIL STOK */
  $stmtStok = $conn->prepare("
    SELECT stok FROM barang WHERE id = ?
  ");
  $stmtStok->bind_param("i", $barang_id);
  $stmtStok->execute();
  $stokData = $stmtStok->get_result()->fetch_assoc();
  $stmtStok->close();

  if (!$stokData || $stokData['stok'] < $jumlah_request) {
    throw new Exception("Stok barang tidak mencukupi.");
  }

  $stok_baru = $stokData['stok'] - $jumlah_request;

  /* UPDATE STOK */
  $stmtUpdateStok = $conn->prepare("
    UPDATE barang SET stok = ? WHERE id = ?
  ");
  $stmtUpdateStok->bind_param("ii", $stok_baru, $barang_id);
  $stmtUpdateStok->execute();
  $stmtUpdateStok->close();

  /* =========================
     NOTIFIKASI
  ========================= */

  /* PESAN ADMIN */
  $pesan_admin =
    "Distribusi baru dibuat oleh Super Admin\n\n" .
    "Kode Distribusi: $kode\n" .
    "Kode Permintaan: {$detail['kode_permintaan']}\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah_request\n" .
    "Kurir: $kurir\n" .
    "No Resi: $no_resi\n" .
    "Alamat Kirim: $alamat\n" .
    "Status: Dikirim";

  /* PESAN CUSTOMER */
  $pesan_customer =
    "Halo {$detail['customer_name']},\n\n" .
    "Barang permintaan Anda telah dikirim.\n\n" .
    "Kode Distribusi: $kode\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah_request\n" .
    "Kurir: $kurir\n" .
    "No Resi: $no_resi\n" .
    "Alamat Pengiriman: $alamat\n\n" .
    "Silakan tunggu hingga barang diterima.";

  /* KIRIM KE CUSTOMER */
  insertNotifikasi(
    $conn,
    $detail['customer_id'],
    $superadmin_id,
    $permintaan_id,
    $pesan_admin,
    $pesan_customer
  );

  $conn->commit();

  header("Location: distribution.php?success=item_distribution_success&kode=$kode&pdf=true");
  exit;
} catch (Exception $e) {
  $conn->rollback();
  header("Location: distribution.php?error=" . urlencode($e->getMessage()));
  exit;
}
