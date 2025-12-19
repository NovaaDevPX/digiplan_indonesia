<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_GET['id'])) {
  die('❌ ID pengadaan tidak ditemukan');
}

$pengadaan_id   = (int) $_GET['id'];
$admin_login_id = (int) $_SESSION['user_id']; // PEMBUAT NOTIFIKASI

/* =========================
   AMBIL DATA PENGADAAN
========================= */
$q = $conn->prepare("
  SELECT 
    pg.*,
    pb.id AS permintaan_id
  FROM pengadaan_barang pg
  JOIN permintaan_barang pb ON pg.permintaan_id = pb.id
  WHERE pg.id = ?
    AND pg.status_pengadaan = 'diproses'
");
$q->bind_param("i", $pengadaan_id);
$q->execute();
$pengadaan = $q->get_result()->fetch_assoc();

if (!$pengadaan) {
  die('❌ Data pengadaan tidak valid atau sudah selesai');
}

$conn->begin_transaction();

try {

  /* =========================
     CEK BARANG (SUDAH ADA?)
  ========================= */
  $cek = $conn->prepare("
    SELECT id, stok
    FROM barang
    WHERE nama_barang = ?
      AND merk = ?
      AND warna = ?
      AND deleted_at IS NULL
    LIMIT 1
  ");
  $cek->bind_param(
    "sss",
    $pengadaan['nama_barang'],
    $pengadaan['merk'],
    $pengadaan['warna']
  );
  $cek->execute();
  $barang = $cek->get_result()->fetch_assoc();

  if ($barang) {
    /* =========================
       UPDATE STOK BARANG
    ========================= */
    $barang_id = $barang['id'];
    $stok_baru = $barang['stok'] + $pengadaan['jumlah'];

    $upBarang = $conn->prepare("
      UPDATE barang
      SET stok = ?, harga = ?
      WHERE id = ?
    ");
    $upBarang->bind_param(
      "idi",
      $stok_baru,
      $pengadaan['harga_satuan'],
      $barang_id
    );
    $upBarang->execute();
  } else {
    /* =========================
       INSERT BARANG BARU
    ========================= */
    $insBarang = $conn->prepare("
      INSERT INTO barang (
        nama_barang,
        merk,
        warna,
        deskripsi,
        stok,
        harga
      ) VALUES (?,?,?,?,?,?)
    ");
    $deskripsi = 'Barang hasil pengadaan';
    $insBarang->bind_param(
      "ssssid",
      $pengadaan['nama_barang'],
      $pengadaan['merk'],
      $pengadaan['warna'],
      $deskripsi,
      $pengadaan['jumlah'],
      $pengadaan['harga_satuan']
    );
    $insBarang->execute();

    $barang_id = $conn->insert_id;
  }

  /* =========================
     UPDATE PENGADAAN
  ========================= */
  $upPengadaan = $conn->prepare("
    UPDATE pengadaan_barang
    SET 
      status_pengadaan = 'selesai',
      barang_id = ?
    WHERE id = ?
  ");
  $upPengadaan->bind_param("ii", $barang_id, $pengadaan_id);
  $upPengadaan->execute();

  /* =========================
     UPDATE PERMINTAAN
  ========================= */
  $upPermintaan = $conn->prepare("
    UPDATE permintaan_barang
    SET status = 'siap_distribusi'
    WHERE id = ?
  ");
  $upPermintaan->bind_param("i", $pengadaan['permintaan_id']);
  $upPermintaan->execute();

  /* =========================
     NOTIFIKASI
     (ADMIN SELESAIKAN PENGADAAN)
  ========================= */
  $pesan =
    "Pengadaan barang dengan\n" .
    "Kode Pengadaan: {$pengadaan['kode_pengadaan']}\n" .
    "Barang: {$pengadaan['nama_barang']}\n" .
    "Merk: {$pengadaan['merk']}\n" .
    "Warna: {$pengadaan['warna']}\n" .
    "Jumlah Masuk: {$pengadaan['jumlah']}\n" .
    "Harga Satuan: " . number_format($pengadaan['harga_satuan'], 0, ',', '.') . "\n" .
    "Total Harga: " . number_format($pengadaan['harga_total'], 0, ',', '.') . "\n" .
    "Status: Barang masuk gudang → Siap Distribusi";

  insertNotifikasiDB(
    $conn,
    $admin_login_id,
    $pengadaan['permintaan_id'],
    $pesan
  );

  /* =========================
     COMMIT
  ========================= */
  $conn->commit();

  header('Location: procurement.php?success=barang_masuk');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
