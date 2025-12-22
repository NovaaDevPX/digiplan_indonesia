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

$pengadaan_id  = (int) $_GET['id'];
$admin_id = (int) $_SESSION['user_id'];

/* =========================
   AMBIL DATA PENGADAAN
========================= */
$q = $conn->prepare("
  SELECT 
    pg.*,
    pb.id       AS permintaan_id,
    pb.user_id  AS customer_id,
    pb.kode_permintaan,
    u.name      AS customer_name
  FROM pengadaan_barang pg
  JOIN permintaan_barang pb ON pg.permintaan_id = pb.id
  JOIN users u ON pb.user_id = u.id
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
    /* UPDATE STOK BARANG */
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
    /* INSERT BARANG BARU */
    $deskripsi = 'Barang hasil pengadaan';

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
    SET status_pengadaan = 'selesai',
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

  /* ==================================================
     NOTIFIKASI 1 — CUSTOMER
  ================================================== */
  $pesan_customer =
    "Halo {$pengadaan['customer_name']},\n\n" .
    "Pengadaan barang untuk permintaan Anda telah selesai.\n\n" .
    "Kode Permintaan: {$pengadaan['kode_permintaan']}\n" .
    "Barang: {$pengadaan['nama_barang']}\n" .
    "Saat ini barang siap untuk proses distribusi.";

  insertNotifikasi(
    $conn,
    $pengadaan['customer_id'],   // receiver customer
    $admin_id,              // sender admin
    $pengadaan['permintaan_id'],
    '',                           // pesan admin dikosongkan
    $pesan_customer
  );

  /* ==================================================
     NOTIFIKASI 2 — INTERNAL (ADMIN / SUPER ADMIN)
  ================================================== */
  $pesan_admin =
    "Pengadaan telah diselesaikan.\n\n" .
    "Kode Pengadaan: {$pengadaan['kode_pengadaan']}\n" .
    "Kode Permintaan: {$pengadaan['kode_permintaan']}\n" .
    "Customer: {$pengadaan['customer_name']}\n" .
    "Barang: {$pengadaan['nama_barang']}\n" .
    "Jumlah Masuk: {$pengadaan['jumlah']}\n\n" .
    "Status: SIAP DISTRIBUSI";

  $qInternal = $conn->query("
    SELECT id FROM users
    WHERE role_id IN (2,3)
      AND deleted_at IS NULL
    LIMIT 1
  ");

  if ($qInternal && $internal = $qInternal->fetch_assoc()) {
    insertNotifikasi(
      $conn,
      $internal['id'], // receiver internal
      $admin_id,  // sender
      $pengadaan['permintaan_id'],
      $pesan_admin,
      ''
    );
  }

  $conn->commit();

  header('Location: procurement.php?success=barang_masuk');
  exit;
} catch (Throwable $e) {
  $conn->rollback();
  die('❌ ERROR DATABASE: ' . $e->getMessage());
}
