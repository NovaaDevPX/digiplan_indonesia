<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

/* =========================
   VALIDASI REQUEST
========================= */
if (
  !isset($_POST['data'], $_POST['kode_distribusi'], $_POST['alamat'], $_POST['kurir'])
) {
  header("Location: distribution.php?error=invalid_request");
  exit;
}

list($pengadaan_id, $permintaan_id) = array_map(
  'intval',
  explode('|', $_POST['data'])
);

$kode   = trim($_POST['kode_distribusi']);
$alamat = trim($_POST['alamat']);
$kurir  = strtoupper(str_replace(' ', '', $_POST['kurir']));
$superadmin_id = (int) ($_SESSION['user_id'] ?? 0);

/* =========================
   GENERATE NO RESI
========================= */
$no_resi = sprintf(
  "RESI-%s-%s-%04d",
  $kurir,
  date('Ymd'),
  random_int(1000, 9999)
);

/* =========================
   AMBIL DETAIL PERMINTAAN + HARGA
========================= */
$stmtDetail = $conn->prepare("
  SELECT 
    b.id AS barang_id,
    b.nama_barang,
    b.harga AS harga_barang,
    pm.jumlah,
    pm.kode_permintaan,
    pm.user_id AS customer_id,
    u.name AS customer_name,
    pg.harga_satuan AS harga_pengadaan
  FROM permintaan_barang pm
  JOIN barang b
    ON b.nama_barang = pm.nama_barang
   AND b.merk = pm.merk
   AND b.warna = pm.warna
  LEFT JOIN pengadaan_barang pg ON pg.id = ?
  JOIN users u ON pm.user_id = u.id
  WHERE pm.id = ?
");
$stmtDetail->bind_param("ii", $pengadaan_id, $permintaan_id);
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
   TENTUKAN HARGA DISTRIBUSI
========================= */
if (!empty($detail['harga_pengadaan']) && $detail['harga_pengadaan'] > 0) {
  $harga_satuan = (float) $detail['harga_pengadaan'];
  $sumber_harga = 'pengadaan';
} else {
  $harga_satuan = (float) $detail['harga_barang'];
  $sumber_harga = 'stok_gudang';
}

$harga_total = $harga_satuan * $jumlah_request;

/* =========================
   TRANSAKSI DATABASE
========================= */
$conn->begin_transaction();

try {

  /* INSERT DISTRIBUSI */
  $stmtInsert = $conn->prepare("
    INSERT INTO distribusi_barang
      (kode_distribusi, pengadaan_id, permintaan_id, admin_id,
       alamat_pengiriman, kurir, no_resi, tanggal_kirim, status_distribusi,
       harga_satuan, harga_total, sumber_harga)
    VALUES
      (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'dikirim', ?, ?, ?)
  ");
  $stmtInsert->bind_param(
    "siiisssdds",
    $kode,
    $pengadaan_id,
    $permintaan_id,
    $superadmin_id,
    $alamat,
    $kurir,
    $no_resi,
    $harga_satuan,
    $harga_total,
    $sumber_harga
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

  /* CEK & UPDATE STOK */
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
  $stmtUpdateStok = $conn->prepare("
    UPDATE barang SET stok = ? WHERE id = ?
  ");
  $stmtUpdateStok->bind_param("ii", $stok_baru, $barang_id);
  $stmtUpdateStok->execute();
  $stmtUpdateStok->close();

  /* =========================
     NOTIFIKASI CUSTOMER
  ========================= */
  $pesan_customer =
    "Halo {$detail['customer_name']},\n\n" .
    "Barang permintaan Anda telah dikirim.\n\n" .
    "Kode Distribusi: $kode\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah_request\n" .
    "Kurir: $kurir\n" .
    "No Resi: $no_resi\n\n" .
    "Silakan konfirmasi jika barang telah diterima.";

  insertNotifikasi(
    $conn,
    $detail['customer_id'],
    $superadmin_id,
    $permintaan_id,
    '',
    $pesan_customer
  );

  /* =========================
     NOTIFIKASI INTERNAL
  ========================= */
  $pesan_admin =
    "Distribusi berhasil dibuat.\n\n" .
    "Kode Distribusi: $kode\n" .
    "Barang: $nama_barang\n" .
    "Jumlah: $jumlah_request\n" .
    "Sumber Harga: " . strtoupper($sumber_harga);

  insertNotifikasi(
    $conn,
    $superadmin_id,
    $superadmin_id,
    $permintaan_id,
    $pesan_admin,
    null
  );

  $conn->commit();

  header("Location: distribution.php?success=item_distribution_success&kode=$kode&pdf=true");
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  header("Location: distribution.php?error=" . urlencode($e->getMessage()));
  exit;
}
