<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: procurement.php');
  exit;
}

/* =========================
   DATA LOGIN
========================= */
$superadmin_id = (int) $_SESSION['user_id']; // SENDER NOTIFIKASI

$barang_id = !empty($_POST['barang_id'])
  ? (int) $_POST['barang_id']
  : null;

/* =========================
   DATA FORM
========================= */
$admin_id      = (int) $_POST['admin_id'];      // ADMIN PENERIMA NOTIF
$permintaan_id = (int) $_POST['permintaan_id'];
$jumlah        = (int) $_POST['jumlah'];
$harga_satuan  = (float) $_POST['harga_satuan'];

$supplier = trim($_POST['supplier']);
$kontak   = trim($_POST['kontak_supplier']);
$alamat   = trim($_POST['alamat_supplier']);

/* =========================
   VALIDASI DASAR
========================= */
if (
  $superadmin_id <= 0 ||
  $admin_id <= 0 ||
  $permintaan_id <= 0 ||
  $jumlah <= 0 ||
  $harga_satuan <= 0 ||
  $supplier === '' ||
  $kontak === '' ||
  $alamat === ''
) {
  die('❌ Data tidak valid');
}

/* =========================
   VALIDASI PERMINTAAN
========================= */
$q = $conn->prepare("
  SELECT 
    pb.jumlah,
    pb.nama_barang,
    pb.merk,
    pb.warna,
    pb.user_id,
    u.name AS customer_name
  FROM permintaan_barang pb
  JOIN users u ON pb.user_id = u.id
  WHERE pb.id = ?
    AND pb.status = 'disetujui'
");
$q->bind_param("i", $permintaan_id);
$q->execute();
$permintaan = $q->get_result()->fetch_assoc();

if (!$permintaan) {
  die('❌ Permintaan tidak valid atau belum disetujui');
}

if ($jumlah < $permintaan['jumlah']) {
  die('❌ Jumlah pengadaan tidak boleh kurang dari permintaan');
}

/* =========================
   HITUNG & KODE PENGADAAN
========================= */
$harga_total = $jumlah * $harga_satuan;

$r = $conn->query("SELECT COUNT(*) total FROM pengadaan_barang");
$total = $r->fetch_assoc()['total'] + 1;
$kode  = 'PGD-' . str_pad($total, 3, '0', STR_PAD_LEFT);

$conn->begin_transaction();

try {

  /* =========================
     INSERT PENGADAAN
  ========================= */
  $stmt = $conn->prepare("
    INSERT INTO pengadaan_barang (
      kode_pengadaan,
      permintaan_id,
      admin_id,
      barang_id,
      nama_barang,
      merk,
      warna,
      jumlah,
      supplier,
      kontak_supplier,
      alamat_supplier,
      harga_satuan,
      harga_total,
      status_pengadaan,
      tanggal_pengadaan
    ) VALUES (
      ?, ?, ?, ?,
      ?, ?, ?, ?,
      ?, ?, ?,
      ?, ?,
      'diproses',
      CURDATE()
    )
  ");

  $stmt->bind_param(
    "siiisssisssdd",
    $kode,
    $permintaan_id,
    $admin_id,
    $barang_id,
    $permintaan['nama_barang'],
    $permintaan['merk'],
    $permintaan['warna'],
    $jumlah,
    $supplier,
    $kontak,
    $alamat,
    $harga_satuan,
    $harga_total
  );

  $stmt->execute();

  /* =========================
     UPDATE STATUS PERMINTAAN
  ========================= */
  $up = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'dalam_pengadaan'
    WHERE id = ?
  ");
  $up->bind_param("i", $permintaan_id);
  $up->execute();

  /* =========================
     NOTIFIKASI KE ADMIN
  ========================= */
  $pesan_admin =
    "Pengadaan barang telah dibuat oleh Super Admin.\n\n" .
    "Kode Pengadaan: $kode\n" .
    "Barang: {$permintaan['nama_barang']}\n" .
    "Merk: {$permintaan['merk']}\n" .
    "Warna: {$permintaan['warna']}\n" .
    "Jumlah: $jumlah\n" .
    "Harga Satuan: Rp " . number_format($harga_satuan, 0, ',', '.') . "\n" .
    "Total Harga: Rp " . number_format($harga_total, 0, ',', '.') . "\n\n" .
    "Status: Disetujui → Dalam Pengadaan";

  /* =========================
     NOTIFIKASI KE CUSTOMER
  ========================= */
  $pesan_customer =
    "Halo {$permintaan['customer_name']},\n\n" .
    "Permintaan barang Anda telah masuk tahap pengadaan.\n\n" .
    "Barang: {$permintaan['nama_barang']}\n" .
    "Kami akan menginformasikan kembali setelah barang siap didistribusikan.";

  insertNotifikasi(
    $conn,
    $permintaan['user_id'], // receiver CUSTOMER
    $superadmin_id,
    $permintaan_id,
    $pesan_admin,           // fallback
    $pesan_customer
  );

  /* =========================
     COMMIT
  ========================= */
  $conn->commit();

  header('Location: procurement.php?success=item_procurement_success');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
